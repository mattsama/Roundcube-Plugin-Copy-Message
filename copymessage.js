function rcmail_copyto() {
	if (rcmail.env.rcm_destfolder == rcmail.env.mailbox)
		return;

	if (!rcmail.env.uid && (!rcmail.message_list || !rcmail.message_list.get_selection().length))
		return;

	var uids = rcmail.env.uid ? rcmail.env.uid : rcmail.message_list.get_selection().join(',');

	rcmail.set_busy(true, 'copymessage.copyingmessage');
	rcmail.http_post('plugin.copymessage', '_uid='+uids+'&_target_mbox='+urlencode(rcmail.env.rcm_destfolder)+'&_from='+urlencode(rcmail.env.mailbox), true);
}

$(document).ready(function(){
	if (window.rcm_contextmenu_register_command) {
		rcm_contextmenu_register_command('copyto', 'rcmail_copyto', $('#rcmContextCopy'), 'moreacts', 'after', true);
	}
});