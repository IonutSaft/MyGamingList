<!DOCTYPE html>
<html>
<head>
    <title>Messages</title>
    <style>
        #users { width: 200px; float: left; border-right: 1px solid #ccc; height: 90vh; overflow-y: auto;}
        #chat { margin-left: 210px; }
        #chatbox { height: 70vh; overflow-y: auto; border: 1px solid #eee; margin-bottom: 10px; padding: 5px;}
        .me { color: blue; }
        .them { color: green; }
        .user-list-item {
            display: flex; align-items: center; gap: 8px; margin-bottom: 6px; position: relative; border: none; background: transparent; cursor: pointer; width: 100%; text-align: left; padding: 6px 2px;
        }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%; object-fit: cover;
        }
        .user-label { flex:1; }
        .user-name { font-weight: 600; }
        .last-message { font-size: 0.92em; color: #666; }
        .unread-badge {
            background: #e74c3c; color: #fff; border-radius: 12px; font-size: 0.8em;
            padding: 2px 8px; margin-left: 8px; min-width:18px; text-align:center; font-weight: 700;
        }
        .msg-sender-header {
            display: flex; align-items: center; gap: 7px; margin-top: 12px; margin-bottom: 2px;
        }
        .chat-avatar {
            width: 28px; height: 28px; border-radius: 50%; object-fit: cover;
        }
        .chat-username {
            font-weight: 600; color: #444; font-size: 1em;
        }
        .msg-bubble {
            margin-left: 36px; /* align with avatar */
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div id="users"></div>
    <div id="chat">
        <div id="chatbox"></div>
        <form id="sendForm" style="display:none;">
            <input type="text" id="msg" autocomplete="off" placeholder="Type a message..." />
            <button type="submit">Send</button>
        </form>
    </div>
    <script src="scripts/messages.js"></script>
</body>
</html>