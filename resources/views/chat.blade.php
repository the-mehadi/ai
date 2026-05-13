<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Marked.js for Markdown to HTML conversion -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <title>Modern AI Chat - Streaming</title>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); }
        #chat::-webkit-scrollbar { width: 5px; }
        #chat::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }

        /* Markdown Styling */
        .bot-content p { margin-bottom: 1rem; }
        .bot-content strong { font-weight: 600; color: #1e293b; }
        .bot-content ul, .bot-content ol { margin-left: 1.5rem; margin-bottom: 1rem; list-style-type: disc; }
        .bot-content li { margin-bottom: 0.5rem; }

        .cursor { display: inline-block; width: 8px; height: 18px; background: #6366f1; margin-left: 4px; vertical-align: middle; }
    </style>
</head>
<body class="bg-slate-50 h-screen flex flex-col text-slate-800">

    <!-- Header -->
    <header class="glass sticky top-0 z-10 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
            <h1 class="text-xl font-semibold tracking-tight text-slate-900">AI Assistant</h1>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[10px] font-bold bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded uppercase tracking-wider">Gemma 3:1B</span>
            <span class="text-[10px] font-bold bg-green-100 text-green-600 px-2 py-0.5 rounded uppercase tracking-wider">Live Stream</span>
        </div>
    </header>

    <!-- Chat Container -->
    <main id="chat" class="flex-1 overflow-y-auto px-4 md:px-20 py-8 space-y-6 flex flex-col">
        <!-- Default Welcome Message -->
        <div class="flex items-start gap-4 max-w-3xl">
            <div class="w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center text-white shrink-0 shadow-lg">AI</div>
            <div class="bg-white p-4 rounded-2xl rounded-tl-none shadow-sm border border-slate-100 text-[15px]">
                হ্যালো! আমি আপনাকে আজ কীভাবে সাহায্য করতে পারি?
            </div>
        </div>
    </main>

    <!-- Input Area -->
    <footer class="p-4 md:px-20 md:pb-8 bg-transparent">
        <form id="chatForm" class="max-w-4xl mx-auto relative group">
            <input id="input"
                type="text"
                autocomplete="off"
                placeholder="Ask me anything..."
                class="w-full bg-white border border-slate-200 rounded-2xl pl-5 pr-14 py-4 shadow-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition-all placeholder:text-slate-400"
            />
            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 bg-indigo-600 text-white p-2.5 rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            </button>
        </form>
        <p class="text-center text-[10px] text-slate-400 mt-3">AI-generated content may be incorrect. Check important info.</p>
    </footer>

    <script>
        const chatForm = document.getElementById('chatForm');
        const chatContainer = document.getElementById('chat');
        const inputField = document.getElementById('input');

        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const message = inputField.value.trim();
            if (!message) return;


            appendMessage('user', message);
            inputField.value = '';


            const botMsgId = 'bot-' + Date.now();
            appendMessage('bot', '', botMsgId);
            const botMsgDiv = document.getElementById(botMsgId).querySelector('.bot-content');
            const cursor = document.getElementById(botMsgId).querySelector('.cursor');

            let fullBotResponse = "";

            try {
                const response = await fetch("{{ route('chatting') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ input: message })
                });

                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let partialData = "";

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    const chunk = decoder.decode(value, { stream: true });
                    partialData += chunk;

                    const lines = partialData.split('\n');
                    partialData = lines.pop();

                    for (const line of lines) {
                        const cleanLine = line.trim();
                        if (cleanLine.startsWith('data: ')) {
                            try {
                                const jsonStr = cleanLine.substring(6);
                                const data = JSON.parse(jsonStr);

                                if (data.type === 'text_delta' && data.delta) {

                                    fullBotResponse += data.delta;


                                    botMsgDiv.innerHTML = marked.parse(fullBotResponse);


                                    chatContainer.scrollTop = chatContainer.scrollHeight;
                                }

                                if (data.type === 'stream_end') {
                                    if(cursor) cursor.remove();
                                }
                            } catch (e) {
                                // Incomplete JSON ignore
                            }
                        }
                    }
                }
                if(cursor) cursor.remove();

            } catch (error) {
                console.error('Error:', error);
                botMsgDiv.innerHTML = '<span class="text-red-500">Sorry, something went wrong. Please try again.</span>';
                if(cursor) cursor.remove();
            }
        });

        function appendMessage(sender, text, id = null) {
            const msgDiv = document.createElement('div');
            if(id) msgDiv.id = id;

            if (sender === 'user') {
                msgDiv.className = "flex items-start gap-4 flex-row-reverse max-w-3xl ml-auto animate-in fade-in duration-300";
                msgDiv.innerHTML = `
                    <div class="w-9 h-9 rounded-full bg-slate-800 flex items-center justify-center text-white shrink-0 shadow-lg">U</div>
                    <div class="bg-indigo-600 text-white p-4 rounded-2xl rounded-tr-none shadow-md text-[15px]">
                        ${text}
                    </div>
                `;
            } else {
                msgDiv.className = "flex items-start gap-4 max-w-3xl animate-in fade-in duration-300";
                msgDiv.innerHTML = `
                    <div class="w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center text-white shrink-0 shadow-lg shadow-indigo-100">AI</div>
                    <div class="bg-white p-4 rounded-2xl rounded-tl-none shadow-sm border border-slate-100 text-[15px] min-h-[50px] w-full">
                        <div class="bot-content prose prose-slate max-w-none">
                            ${text}
                        </div>
                        <span class="cursor animate-pulse"></span>
                    </div>
                `;
            }

            chatContainer.appendChild(msgDiv);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    </script>
</body>
</html>
