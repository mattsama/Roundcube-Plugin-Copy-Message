/**
 * CopyMessage plugin script
 */

function rcmail_copyto(command, el, pos) {
	if (rcmail.env.rcm_destfolder == rcmail.env.mailbox)
		return;

	var prev_sel = null;

	// also select childs of (collapsed) threads
	if (rcmail.env.uid) {
		if (!rcmail.message_list.in_selection(rcmail.env.uid)) {
			prev_sel = rcmail.message_list.get_selection();
			rcmail.message_list.select_row(rcmail.env.uid);
		}

		if (rcmail.message_list.rows[rcmail.env.uid].has_children && !rcmail.message_list.rows[rcmail.env.uid].expanded)
			rcmail.message_list.select_childs(rcmail.env.uid);

		rcmail.env.uid = null;
	}

	rcmail.command('copy', rcmail.env.rcm_destfolder, $(el));

	if (prev_sel) {
		rcmail.message_list.clear_selection();

		for (var i in prev_sel)
			rcmail.message_list.select_row(prev_sel[i], CONTROL_KEY);
	}

	delete rcmail.env.rcm_destfolder;
}

$(document).ready(function(){
	if (window.rcm_contextmenu_register_command) {
		rcm_contextmenu_register_command('copy', 'rcmail_copyto', $('#rcmContextCopy'), 'moreacts', 'after', true);
	}
});