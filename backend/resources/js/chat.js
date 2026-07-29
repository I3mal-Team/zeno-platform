import Echo from './echo';

// Progressive enhancement for the two-pane chat: with JS, an open thread streams
// new messages over Reverb and posts without a full reload; without it, the
// server-rendered form still works via the classic redirect. The markup is
// view-owned — this only clones the view's own <template> so employer and site
// keep their distinct bubbles.
const thread = document.querySelector('[data-thread]');

if (thread && window.Echo) {
    const uuid = thread.dataset.conversation;
    const me = thread.dataset.me;
    const form = document.querySelector('[data-composer]');
    const input = form?.querySelector('input[name="body"]');

    const timeIn = (iso) => {
        try {
            return new Intl.DateTimeFormat('en-GB', {
                hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'Asia/Riyadh',
            }).format(new Date(iso));
        } catch {
            return '';
        }
    };

    const stickToBottom = () => { thread.scrollTop = thread.scrollHeight; };

    // Idempotent by message uuid: the sender sees its own message from the POST
    // response and again over the socket — whichever lands first wins, the echo
    // is ignored.
    const render = (msg) => {
        if (!msg?.uuid || thread.querySelector(`[data-uuid="${msg.uuid}"]`)) return;

        const mine = msg.is_mine ?? (msg.sender_id === me);
        const tpl = document.querySelector(`template[data-msg="${mine ? 'mine' : 'them'}"]`);
        if (!tpl?.content.firstElementChild) return;

        const node = tpl.content.firstElementChild.cloneNode(true);
        node.dataset.uuid = msg.uuid;
        const body = node.querySelector('[data-slot="body"]');
        if (body) body.textContent = msg.body ?? '';
        const time = node.querySelector('[data-slot="time"]');
        if (time) time.textContent = timeIn(msg.created_at);

        thread.appendChild(node);
        stickToBottom();
    };

    stickToBottom();

    if (uuid) {
        Echo.private(`conversation.${uuid}`).listen('.message.sent', render);
    }

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const body = input?.value.trim();
        if (!body) return;

        input.value = '';
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ body }),
            });

            if (response.ok) {
                render(await response.json());
            } else {
                input.value = body; // keep the draft so nothing is lost
            }
        } catch {
            input.value = body;
        }
    });
}
