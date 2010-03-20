function rcmail_copyto(command, el, pos) {
	if (rcmail.env.rcm_destfolder == rcmail.env.mailbox)
		return;

	rcmail.command('copy', rcmail.env.rcm_destfolder, $(el));
	rcmail.env.rcm_destfolder = null;
}

$(document).ready(function(){
	if (window.rcm_contextmenu_register_command) {
		rcm_contextmenu_register_command('copy', 'rcmail_copyto', $('#rcmContextCopy'), 'moreacts', 'after', true);
	}
});