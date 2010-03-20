function rcmail_copyto(command, el, pos) {
	if (rcmail.env.rcm_destfolder == rcmail.env.mailbox)
		return;

	// also select childs of (collapsed) threads for dragging
	if (rcmail.message_list.rows[rcmail.env.uid].has_children) {
		rcmail.message_list.select_row(rcmail.env.uid, CONTROL_KEY);
		rcmail.env.uid = null;

		var selection = $.merge([], rcmail.message_list.selection);
		var depth, row, uid, r;
		for (var n=0; n < selection.length; n++) {
			uid = selection[n];
			if (rcmail.message_list.rows[uid].has_children /*&& !this.rows[uid].expanded*/) {
				depth = rcmail.message_list.rows[uid].depth;
				row = rcmail.message_list.rows[uid].obj.nextSibling;

				while (row) {
					if (row.nodeType == 1) {
						if ((r = rcmail.message_list.rows[row.uid])) {
							if (!r.depth || r.depth <= depth)
								break;

							if (!rcmail.message_list.in_selection(r.uid))
								rcmail.message_list.select_row(r.uid, CONTROL_KEY);
						}
					}

					row = row.nextSibling;
				}
			}
		}
	}

	rcmail.command('copy', rcmail.env.rcm_destfolder, $(el));
	rcmail.env.rcm_destfolder = null;
}

$(document).ready(function(){
	if (window.rcm_contextmenu_register_command) {
		rcm_contextmenu_register_command('copy', 'rcmail_copyto', $('#rcmContextCopy'), 'moreacts', 'after', true);
	}
});