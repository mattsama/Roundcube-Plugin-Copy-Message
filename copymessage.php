<?php

/**
 * CopyMessage
 *
 * Plugin to allow message to be copied to different folders
 *
 * @version @package_version@
 * @requires ContextMenu plugin
 * @author Philip Weir
 */
class copymessage extends rcube_plugin
{
	public $task = 'mail';

	function init()
	{
		// load required plugin
		$this->require_plugin('contextmenu');

		$rcmail = rcube::get_instance();
		if ($rcmail->action == '')
			$this->add_hook('render_mailboxlist', array($this, 'show_copy_contextmenu'));
	}

	public function show_copy_contextmenu($args)
	{
		$rcmail = rcube::get_instance();
		$this->add_texts('localization/');
		$this->api->output->add_label('copymessage.copyingmessage');
		$this->include_script('copymessage.js');

		$li = html::tag('li', array('class' => 'submenu copyto'), html::span(null, rcmail::Q($this->gettext('copyto'))) . $this->_gen_folder_list($args['list'], '#copy'));
		$out .= html::tag('ul', array('id' => 'rcmContextCopy'), $li);
		$this->api->output->add_footer(html::div(array('style' => 'display: none;'), $out));
	}

	// based on rcmail->render_folder_tree_html()
	private function _gen_folder_list($arrFolders, $command, $nestLevel = 0, &$folderTotal = 0)
	{
		$rcmail = rcube::get_instance();

		$maxlength = 35;
		$realnames = false;

		$out = '';
		foreach ($arrFolders as $key => $folder) {
			$title = null;

			if (($folder_class = $rcmail->folder_classname($folder['id'])) && !$realnames) {
				$foldername = $rcmail->gettext($folder_class);
			}
			else {
				$foldername = $folder['name'];

				// shorten the folder name to a given length
				if ($maxlength && $maxlength > 1) {
					$fname = abbreviate_string($foldername, $maxlength);

					if ($fname != $foldername)
						$title = $foldername;

					$foldername = $fname;
				}
			}

			// make folder name safe for ids and class names
			$folder_id = asciiwords($folder['id'], true, '_');
			$classes = array();

			// set special class for Sent, Drafts, Trash and Junk
			if ($folder['id'] == $rcmail->config->get('sent_mbox'))
				$classes[] = 'sent';
			else if ($folder['id'] == $rcmail->config->get('drafts_mbox'))
				$classes[] = 'drafts';
			else if ($folder['id'] == $rcmail->config->get('trash_mbox'))
				$classes[] = 'trash';
			else if ($folder['id'] == $rcmail->config->get('junk_mbox'))
				$classes[] = 'junk';
			else if ($folder['id'] == 'INBOX')
				$classes[] = 'inbox';
			else
				$classes[] = '_'.asciiwords($folder_class ? $folder_class : strtolower($folder['id']), true);

			if ($folder['virtual'])
				$classes[] = 'virtual';

			if ($nestLevel > 0)
				$classes[] = 'subfolder';

			$out .= html::tag('li', array('class' => join(' ', $classes)), html::a(array('href' => $command, 'onclick' => "rcm_set_dest_folder('" . rcmail::JQ($folder['id']) ."')", 'class' => 'active', 'title' => $title), html::span(null, str_repeat('&nbsp;&nbsp;', $nestLevel) . rcmail::Q($foldername))));

			if (!empty($folder['folders']))
				$out .= $this->_gen_folder_list($folder['folders'], $command, $nestLevel+1, $folderTotal);

			$folderTotal++;
		}

		if ($nestLevel == 0) {
			if ($folderTotal > 5) {
				$out = html::tag('ul', array('class' => 'toolbarmenu folders scrollable'), $out);
				$out = html::tag('div', array('class' => 'scroll_up_pas'), '') . $out . html::tag('div', array('class' => 'scroll_down_act'), '');
				$out = html::tag('div', array('class' => 'popupmenu'), $out);
			}
			else {
				$out = html::tag('ul', array('class' => 'popupmenu toolbarmenu folders'), $out);
			}
		}

		return $out;
	}
}

?>