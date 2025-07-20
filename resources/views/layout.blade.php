<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap and Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar (menu) -->
        <aside class="bg-light border-end" style="width: 250px; min-height: 100vh;">
            @include('component.menu')
        </aside>

        <!-- Main content -->
        <main class="flex-fill p-4">
            @yield('content')
        </main>
         <div id="floatingAiContainer" class="position-fixed bottom-0 end-0 m-4" style="z-index:9999;">
              <button class="btn btn-primary rounded-circle shadow d-flex align-items-center justify-content-center"
                    id="aiFab" style="width: 60px; height: 60px; font-size: 24px;">
                🤖
              </button>

               <div id="aiModal" class="card shadow-lg mt-2 p-3" style="display: none; width: 500px; max-height: 600px; overflow-y: auto;">
                    <h5 class="mb-3">AI Assistant</h5>
                    <div id="aiAssistantContent">
                        <button class="btn btn-secondary w-100 mb-2" onclick="openMenu()">Choose Action</button>
                        <div id="actionArea" class="mt-3 p-3 border rounded bg-light" style="max-height: 400px; overflow-y: auto;"></div>
                    </div>
                </div>
            </div>


        <div class="modal fade" id="actionModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content p-3">
                    <h5>What would you like to do?</h5>
                    <button class="btn btn-primary my-1" onclick="selectAction('chat')">💬 Chat with AI</button>
                    <button class="btn btn-success my-1" onclick="selectAction('grammar')">✍️ Check Grammar</button>
                    <button class="btn btn-dark my-1" onclick="selectAction('record')">🎥 Record Screen</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JS dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
            document.addEventListener('DOMContentLoaded', function () {
                const fab = document.getElementById('aiFab');
                const modal = document.getElementById('aiModal');
                fab?.addEventListener('click', () => {
                    modal.style.display = modal.style.display === 'none' ? 'block' : 'none';
                });
            });

            function openMenu() {
                new bootstrap.Modal(document.getElementById('actionModal')).show();
            }

            function selectAction(action) {
                const container = document.getElementById('actionArea');
                let html = '';
                switch (action) 
                {
                    case 'chat':
                        html = `
                        <h4>Chat with AI</h4>
                        <form id="chatForm">
                           <textarea id="messageInput" class="form-control" placeholder="Ask something..." style="height: 200px;"></textarea>
                            <button class="btn btn-primary mt-2" type="submit">Send</button>
                        </form>
                        <pre id="responseBox" class="mt-3 p-2 border rounded bg-white"
                        style="white-space: pre-wrap; word-wrap: break-word; overflow-x: auto;"></pre>`;
                        break;
                    case 'grammar':
                        html = `
                        <h4>Grammar Checker</h4>
                        <form id="grammarForm">
                            <textarea id="grammarText" class="form-control" placeholder="Enter your text..." style="height: 200px;"></textarea>
                            <button class="btn btn-success mt-2" type="submit">Check</button>
                        </form>
                        <pre id="grammarResult" class="mt-3 p-2 border rounded bg-white"
                        style="white-space: pre-wrap; word-wrap: break-word; overflow-x: auto;"></pre>`;
                        break;
                    case 'record':
                        html = `
                        <h4>Screen Recorder</h4>
                        <button onclick="startRecording()" class="btn btn-dark">Start Recording</button>
                        <video id="recordedVideo" controls class="mt-2" style="width:100%;"></video>`;
                        break;
                }
                container.innerHTML = html;
                bootstrap.Modal.getInstance(document.getElementById('actionModal')).hide();
                attachEventListeners();
            }

            function attachEventListeners() 
            {
                if (document.getElementById('chatForm')) {
                    document.getElementById('chatForm').addEventListener('submit', async (e) => {
                        e.preventDefault();
                           const res = await fetch("{{ route('ask.chatgpt') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ message: document.getElementById('messageInput').value })
                        });
                        const data = await res.json();
                        document.getElementById('responseBox').textContent = data.choices[0].message.content;
                    });
                }

                if (document.getElementById('grammarForm')) {
                document.getElementById('grammarForm').addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const res = await fetch("{{ route('grammar.check') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ text: document.getElementById('grammarText').value })
                    });

                    const data = await res.json();

                    if (data.response && data.response.errors.length > 0) {
                        const matches = data.response.errors.map(err => 
                            `❌ ${err.description.en} ➡️ ${err.better.join(', ')}`
                        );
                        document.getElementById('grammarResult').textContent = matches.join("\n");
                    } else {
                        document.getElementById('grammarResult').textContent = '✅ No grammar mistakes found.';
                    }
                });
            }

            }
            
            let recorder, stream;

           async function startRecording() 
           {
            try 
            {
                const screenStream = await navigator.mediaDevices.getDisplayMedia({
                    video: true,
                    audio: true 
                });

                const micStream = await navigator.mediaDevices.getUserMedia({ audio: true });

                const combinedStream = new MediaStream([
                    ...screenStream.getVideoTracks(),
                    ...micStream.getAudioTracks()
                ]);

                const chunks = [];
                recorder = new MediaRecorder(combinedStream);

                recorder.ondataavailable = (e) => chunks.push(e.data);

                recorder.onstop = () => {
                    const blob = new Blob(chunks, { type: 'video/webm' });
                    const url = URL.createObjectURL(blob);
                    document.getElementById('recordedVideo').src = url;
                };

                screenStream.getVideoTracks()[0].onended = () => {
                    if (recorder && recorder.state === 'recording') {
                        recorder.stop();
                    }
                };

                recorder.start();
                stream = combinedStream;
            } catch (err) {
                console.error('Screen recording failed:', err);
            }
        }
    </script>
</body>
</html>
