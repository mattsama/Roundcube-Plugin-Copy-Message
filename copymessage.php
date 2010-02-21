<?php

/**
 * CopyMessage
 *
 * Plugin to allow message to be copied to different folders
 *
 * @version 0.2
 * @author Philip Weir
 * @url http://roundcube.net/plugins/copymessage
 */
class copymessage extends rcube_plugin
{
	public $task = 'mail';

	function init()
	{
		$rcmail = rcmail::get_instance();
		if ($rcmail->action == '')
			$this->add_hook('render_mailboxlist', array($this, 'show_copy_contextmenu'));

		$this->register_action('plugin.copymessage.copy', array($this, 'copy_message'));
	}

	public function show_copy_contextmenu($args)
	{
		$rcmail = rcmail::get_instance();
		$this->add_texts('localization/');
		$this->api->output->add_label('copymessage.copyingmessage');
		$this->include_script('copymessage.js');

		$li = html::tag('li', array('class' => 'submenu copyto'), Q($this->gettext('copyto')) . $this->_gen_folder_list($args['list'], '#copyto'));
		$out .= html::tag('ul', array('id' => 'rcmContextCopy'), $li);
		$this->api->output->add_footer(html::div(array('style' => 'display: none;'), $out));
	}

	public function copy_message()
	{
		$this->add_texts('localization/');

		$uids = get_input_value('_uid', RCUBE_INPUT_POST);
		$mbox = get_input_value('_from', RCUBE_INPUT_POST);
		$target = get_input_value('_target_mbox', RCUBE_INPUT_POST);

		$copied = $this->_copy_message($uids, $target, $mbox);

		if (!$copied) {
	        // send error message
			$this->api->output->command('display_message', $this->gettext('errorcopying'), 'error');
	        $this->api->output->send();
    	}
	}

	// based on rcmail_render_folder_tree_html()
	private function _gen_folder_list($arrFolders, $command, $nestLevel = 0) {
		$rcmail = rcmail::get_instance();

		$maxlength = 35;
		$realnames = false;

		$idx = 0;
		$out = '';
		foreach ($arrFolders as $key => $folder) {
			$title = null;

			if (($folder_class = rcmail_folder_classname($folder['id'])) && !$realnames) {
				$foldername = rcube_label($folder_class);
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

			$out .= html::tag('li', array('class' => join(' ', $classes)), html::a(array('href' => $command, 'onclick' => "rcm_set_dest_folder('" . JQ($folder['id']) ."')", 'class' => 'active', 'title' => $title), str_repeat('&nbsp;&nbsp;', $nestLevel) . Q($foldername)));

			if (!empty($folder['folders']))
				$out .= $this->_gen_folder_list($folder['folders'], $command, $nestLevel+1);

			$idx++;
		}

		if ($nestLevel == 0) {
			if ($idx > 5) {
				$out = html::tag('ul', array('class' => 'toolbarmenu folders scrollable'), $out);
				$out = html::tag('div', array('class' => 'scroll_up_pas'), '') . $out . html::tag('div', array('class' => 'scroll_down_act'), '');
			}
			else {
				$out = html::tag('ul', array('class' => 'toolbarmenu folders'), $out);
			}
		}

		return $out;
	}

	private function _copy_message($uids, $to_mbox, $from_mbox='') {
		$imap = rcmail::get_instance()->imap;

		$fbox = $from_mbox;
		$tbox = $to_mbox;
		$to_mbox = $imap->mod_mailbox($to_mbox);
		$from_mbox = $from_mbox ? $imap->mod_mailbox($from_mbox) : $imap->mailbox;

		// make sure mailbox exists
		if ($to_mbox != 'INBOX' && !in_array($tbox, $imap->list_mailboxes()))
		{
			if (in_array($tbox, $imap->default_folders))
				$imap->create_mailbox($tbox, TRUE);
			else
				return FALSE;
		}

		// convert the list of uids to array
		$a_uids = is_string($uids) ? explode(',', $uids) : (is_array($uids) ? $uids : NULL);

		// exit if no message uids are specified
		if (!is_array($a_uids) || empty($a_uids))
			return false;

		// copy messages
		$iil_copy = iil_C_Copy($imap->conn, join(',', $a_uids), $from_mbox, $to_mbox);
		$copied = !($iil_copy === false || $iil_copy < 0);

		return $copied;
	}
}

?>