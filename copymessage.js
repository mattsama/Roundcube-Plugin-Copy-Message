function rcmail_copyto(command, el, pos) {
	if (rcmail.env.rcm_destfolder == rcmail.env.mailbox)
		return;

	// also select childs of (collapsed) threads
	if (rcmail.message_list.rows[rcmail.env.uid].has_children && !rcmail.message_list.rows[rcmail.env.uid].expanded) {
		rcmail.message_list.select_row(rcmail.env.uid, CONTROL_KEY);
		rcmail.message_list.select_childs(rcmail.env.uid);
		rcmail.env.uid = null;
	}

	rcmail.command('copy', rcmail.env.rcm_destfolder, $(el));
	rcmail.env.rcm_destfolder = null;
}

$(document).ready(function(){
	if (window.rcm_contextmenu_register_command) {
		rcm_contextmenu_register_command('copy', 'rcmail_copyto', $('#rcmContextCopy'), 'moreacts', 'after', true);
	}
});