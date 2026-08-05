import streamlit as st
import requests
import json
import subprocess
import base64
import os
try:
    from gTTS import gTTS
except ImportError:
    gTTS = None
import tempfile
import sqlite3
import time

# --- Configuration ---
OLLAMA_BASE = "http://172.27.224.1:11434"
MODEL = "qwen2.5:7b"
API_URL = f"{OLLAMA_BASE}/v1/chat/completions"

st.set_page_config(page_title="ZaneAI: The Child", page_icon="⚡", layout="wide")

# Helper to read base64 image with multi-path resolution
def find_file(filename):
    base_dir = os.path.dirname(os.path.abspath(__file__))
    candidates = [
        os.path.join(base_dir, filename),
        f"/mnt/c/Users/user/Desktop/ZaneX/my-docker-deployment/ZaneAI/{filename}",
        f"/mnt/c/Users/user/Desktop/ZaneX/my-docker-deployment/{filename}",
        f"/mnt/c/Users/user/Desktop/{filename}"
    ]
    for p in candidates:
        if os.path.exists(p):
            return p
    return candidates[0]

def get_base64_image(filename):
    path = find_file(filename)
    if os.path.exists(path):
        with open(path, "rb") as img_file:
            return base64.b64encode(img_file.read()).decode('utf-8')
    return ""

GIF_BG_B64 = get_base64_image("child_bg.gif")
BG_B64 = get_base64_image("child_bg.jpg")
AVATAR_B64 = get_base64_image("child_avatar.jpg")

# --- Persistent Neural Core (SQLite) ---
DB_PATH = find_file("child_memory.db")
conn = sqlite3.connect(DB_PATH, check_same_thread=False)
cursor = conn.cursor()
cursor.execute('''
    CREATE TABLE IF NOT EXISTS memory_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        role TEXT,
        content TEXT
    )
''')
conn.commit()

# Query memory count
cursor.execute("SELECT COUNT(*) FROM memory_logs")
memory_count = cursor.fetchone()[0]

def remember(role, content):
    """Saves a message to both session state and permanent SQLite DB (OpenAI format)"""
    st.session_state.messages.append({"role": role, "content": content})
    cursor.execute("INSERT INTO memory_logs (role, content) VALUES (?, ?)", (role, json.dumps(content)))
    conn.commit()

# --- Advanced Cyberpunk Visual Theme ---
if GIF_BG_B64:
    bg_css_rule = f"background: linear-gradient(180deg, rgba(0, 0, 0, 0.75), rgba(2, 6, 12, 0.88)), url('data:image/gif;base64,{GIF_BG_B64}') no-repeat center center fixed !important; background-size: cover !important;"
elif BG_B64:
    bg_css_rule = f"background: linear-gradient(180deg, rgba(0, 0, 0, 0.82), rgba(2, 6, 12, 0.95)), url('data:image/jpeg;base64,{BG_B64}') no-repeat center center fixed !important; background-size: cover !important;"
else:
    bg_css_rule = "background-color: #000000 !important;"

st.markdown(f"""
<style>
    /* Main Background & Ambient Glow */
    .stApp, .stApp > header {{
        {bg_css_rule}
        color: #00ff41 !important;
        font-family: 'Consolas', 'Courier New', monospace !important;
    }}
    
    * {{
        font-family: 'Consolas', 'Courier New', monospace !important;
        border-radius: 0px !important;
    }}
    
    /* Neon Text & Headers */
    h1, h2, h3, h4, p, span, div, label {{
        color: #00ff41 !important;
        letter-spacing: 1px;
    }}
    
    /* HUD Header Panel */
    .hud-banner {{
        display: flex;
        align-items: center;
        gap: 20px;
        background: rgba(4, 12, 8, 0.85);
        border: 2px solid #00ff41;
        box-shadow: 0 0 20px rgba(0, 255, 65, 0.25), inset 0 0 15px rgba(0, 255, 65, 0.1);
        padding: 20px;
        margin-bottom: 25px;
        position: relative;
        overflow: hidden;
    }}
    
    .hud-banner::after {{
        content: '';
        position: absolute;
        top: 0; right: 0; width: 100px; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 0, 60, 0.2));
        pointer-events: none;
    }}
    
    .avatar-frame {{
        width: 90px;
        height: 90px;
        border: 2px solid #ff003c;
        box-shadow: 0 0 15px #ff003c;
        object-fit: cover;
    }}
    
    .status-badge {{
        display: inline-block;
        padding: 4px 10px;
        font-size: 11px;
        font-weight: bold;
        background: rgba(0, 255, 65, 0.15);
        border: 1px solid #00ff41;
        color: #00ff41;
        margin-right: 8px;
        margin-top: 5px;
        text-shadow: 0 0 5px #00ff41;
    }}
    
    .status-badge.danger {{
        background: rgba(255, 0, 60, 0.2);
        border-color: #ff003c;
        color: #ff003c;
        text-shadow: 0 0 5px #ff003c;
    }}
    
    .status-badge.cyan {{
        background: rgba(0, 229, 255, 0.15);
        border-color: #00e5ff;
        color: #00e5ff;
        text-shadow: 0 0 5px #00e5ff;
    }}
    
    /* Structured Chat Messages */
    .stChatMessage[data-testid="stChatMessage"] {{
        background-color: rgba(2, 8, 4, 0.8) !important;
        border: 1px solid #00ff41 !important;
        padding: 18px !important;
        margin-bottom: 18px !important;
        box-shadow: 0 0 10px rgba(0, 255, 65, 0.1) !important;
    }}
    
    .stChatMessage:nth-child(odd) {{
        border-left: 6px solid #00ff41 !important;
    }}
    
    .stChatMessage:nth-child(even) {{
        border-color: #ff003c !important;
        border-left: 6px solid #ff003c !important;
        background-color: rgba(12, 2, 5, 0.85) !important;
    }}
    
    .stChatMessage:nth-child(even) * {{
        color: #ff003c !important;
    }}
    
    /* Terminal Input Box */
    .stChatInputContainer textarea {{
        background-color: rgba(0, 0, 0, 0.9) !important;
        color: #00ff41 !important;
        border: 2px solid #00ff41 !important;
        box-shadow: 0 0 15px rgba(0, 255, 65, 0.2) !important;
    }}
    
    .stChatInputContainer textarea:focus {{
        border: 2px solid #ff003c !important;
        box-shadow: 0 0 20px rgba(255, 0, 60, 0.4) !important;
    }}
    
    /* Code Blocks */
    pre, code {{
        background-color: #040806 !important;
        color: #00e5ff !important;
        border: 1px dashed #00ff41 !important;
    }}
    
    /* Sidebar Console */
    [data-testid="stSidebar"] {{
        background: rgba(0, 0, 0, 0.92) !important;
        border-right: 2px solid #ff003c !important;
    }}
    
    /* Enhanced Cyberpunk Button Shapes & Upload Button */
    button[kind="secondary"], .stButton > button, [data-testid="stBaseButton-secondary"], [data-testid="stFileUploadDropzone"] button, [data-testid="stAudioInput"] button {{
        background: linear-gradient(135deg, rgba(0, 255, 65, 0.15), rgba(0, 0, 0, 0.95)) !important;
        color: #00ff41 !important;
        border: 2px solid #00ff41 !important;
        clip-path: polygon(12px 0, 100% 0, 100% calc(100% - 12px), calc(100% - 12px) 100%, 0 100%, 0 12px) !important;
        padding: 10px 24px !important;
        font-size: 13px !important;
        font-weight: bold !important;
        letter-spacing: 1.5px !important;
        text-transform: uppercase !important;
        transition: all 0.25s ease-in-out !important;
        box-shadow: 0 0 12px rgba(0, 255, 65, 0.25) !important;
        cursor: pointer !important;
    }}
    
    button[kind="secondary"]:hover, .stButton > button:hover, [data-testid="stBaseButton-secondary"]:hover, [data-testid="stFileUploadDropzone"] button:hover, [data-testid="stAudioInput"] button:hover {{
        background: #ff003c !important;
        color: #000000 !important;
        border-color: #ff003c !important;
        box-shadow: 0 0 25px rgba(255, 0, 60, 0.7) !important;
        transform: translateY(-2px) scale(1.03) !important;
    }}
    
    /* Button inner paragraph <p> and child text elements */
    button[kind="secondary"] *, .stButton > button *, [data-testid="stBaseButton-secondary"] *, [data-testid="stFileUploadDropzone"] button * {{
        color: inherit !important;
        font-family: 'Consolas', 'Courier New', monospace !important;
        font-size: 13px !important;
        font-weight: bold !important;
        letter-spacing: 1.5px !important;
        text-transform: uppercase !important;
        margin: 0 !important;
        padding: 0 !important;
        line-height: 1 !important;
    }}
    
    /* Flex Container for Icon + Text alignment */
    button[kind="secondary"] > div, .stButton > button > div, [data-testid="stBaseButton-secondary"] > div, [data-testid="stFileUploadDropzone"] button > div, [data-testid="stBaseButton-secondary"] span {{
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
    }}
    
    /* Icon Styling & Font Restoration (upload material icon) */
    [data-testid="stIconMaterial"], [data-testid="stIconMaterial"] * {{
        font-family: 'Material Symbols Rounded', 'Material Symbols Outlined', 'Material Icons', sans-serif !important;
        font-size: 18px !important;
        font-weight: normal !important;
        font-style: normal !important;
        line-height: 1 !important;
        letter-spacing: normal !important;
        text-transform: none !important;
        color: inherit !important;
        display: inline-block !important;
        vertical-align: middle !important;
        margin-right: 4px !important;
    }}
    
    /* Upload Dropzone Container */
    [data-testid="stFileUploadDropzone"] {{
        background-color: rgba(0, 0, 0, 0.85) !important;
        border: 2px dashed #00ff41 !important;
        clip-path: polygon(15px 0, 100% 0, 100% calc(100% - 15px), calc(100% - 15px) 100%, 0 100%, 0 15px) !important;
        padding: 20px !important;
        box-shadow: inset 0 0 15px rgba(0, 255, 65, 0.1) !important;
    }}
    
    [data-testid="stFileUploadDropzone"] * {{
        color: #00ff41 !important;
    }}
    
    .stChatMessageAvatar {{
        background-color: transparent !important;
    }}
</style>
""", unsafe_allow_html=True)

# Up Arrow Javascript Hook & Matrix Rain & Konami Code
import streamlit.components.v1 as components
components.html("""
<script>
const doc = window.parent.document;
if (!window.parent.window.cmdHistoryAttached) {
    window.parent.window.cmdHistoryAttached = true;
    let cmdHistory = JSON.parse(sessionStorage.getItem("cmdHistory")) || [];
    let historyIndex = cmdHistory.length;

    doc.addEventListener('keydown', function(e) {
        const target = e.target;
        if (target.tagName === 'TEXTAREA' && target.dataset.testid === 'stChatInputTextArea') {
            if (e.key === 'Enter' && !e.shiftKey) {
                const val = target.value.trim();
                if (val && val !== cmdHistory[cmdHistory.length - 1]) {
                    cmdHistory.push(val);
                    sessionStorage.setItem("cmdHistory", JSON.stringify(cmdHistory));
                }
                historyIndex = cmdHistory.length;
            } else if (e.key === 'ArrowUp') {
                if (historyIndex > 0) {
                    e.preventDefault();
                    historyIndex--;
                    setNativeValue(target, cmdHistory[historyIndex]);
                }
            } else if (e.key === 'ArrowDown') {
                if (historyIndex < cmdHistory.length - 1) {
                    e.preventDefault();
                    historyIndex++;
                    setNativeValue(target, cmdHistory[historyIndex]);
                } else if (historyIndex === cmdHistory.length - 1) {
                    e.preventDefault();
                    historyIndex++;
                    setNativeValue(target, "");
                }
            }
        }
    });

    function setNativeValue(element, value) {
        const valueSetter = Object.getOwnPropertyDescriptor(window.HTMLTextAreaElement.prototype, "value").set;
        const prototype = Object.getPrototypeOf(element);
        const prototypeValueSetter = Object.getOwnPropertyDescriptor(prototype, "value").set;
        if (valueSetter && valueSetter !== prototypeValueSetter) {
            prototypeValueSetter.call(element, value);
        } else {
            valueSetter.call(element, value);
        }
        element.dispatchEvent(new Event('input', { bubbles: true }));
    }
}

if (!window.parent.window.matrixRainAttached) {
    window.parent.window.matrixRainAttached = true;
    const canvas = doc.createElement('canvas');
    canvas.id = 'matrixCanvas';
    canvas.style.position = 'fixed';
    canvas.style.top = '0';
    canvas.style.left = '0';
    canvas.style.width = '100vw';
    canvas.style.height = '100vh';
    canvas.style.zIndex = '-1';
    canvas.style.opacity = '0.18';
    canvas.style.pointerEvents = 'none';
    doc.body.insertBefore(canvas, doc.body.firstChild);

    const ctx = canvas.getContext('2d');
    canvas.width = window.parent.innerWidth;
    canvas.height = window.parent.innerHeight;

    const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789$+-*/=%""\'#&_(),.;:?!\\\\|{}<>[]^~';
    const fontSize = 16;
    const columns = canvas.width / fontSize;
    const drops = [];
    for(let x = 0; x < columns; x++) drops[x] = 1;

    function draw() {
        if (!window.parent.window.matrixRainAttached) return;
        ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        ctx.fillStyle = '#00ff41';
        ctx.font = fontSize + 'px monospace';
        
        for(let i = 0; i < drops.length; i++) {
            const text = letters.charAt(Math.floor(Math.random() * letters.length));
            ctx.fillText(text, i * fontSize, drops[i] * fontSize);
            
            if(drops[i] * fontSize > canvas.height && Math.random() > 0.975)
                drops[i] = 0;
            drops[i]++;
        }
    }
    setInterval(draw, 33);
}

// Secret Room Trigger (Left, Right, z, x, c, v, b, n, m)
if (!window.parent.window.konamiAttached) {
    window.parent.window.konamiAttached = true;
    const konamiCode = ['ArrowLeft', 'ArrowRight', 'z', 'x', 'c', 'v', 'b', 'n', 'm'];
    let konamiIndex = 0;
    
    doc.addEventListener('keydown', function(e) {
        if (e.key === konamiCode[konamiIndex]) {
            konamiIndex++;
            if (konamiIndex === konamiCode.length) {
                activateSecretRoom();
                konamiIndex = 0;
            }
        } else {
            konamiIndex = 0;
        }
    });

    function activateSecretRoom() {
        window.parent.window.matrixRainAttached = false;
        const oldCanvas = doc.getElementById('matrixCanvas');
        if (oldCanvas) oldCanvas.remove();
        if (doc.getElementById('tunnelCanvas')) return;

        const canvas = doc.createElement('canvas');
        canvas.id = 'tunnelCanvas';
        canvas.style.position = 'fixed';
        canvas.style.top = '0';
        canvas.style.left = '0';
        canvas.style.width = '100vw';
        canvas.style.height = '100vh';
        canvas.style.zIndex = '-1';
        canvas.style.opacity = '0.35';
        doc.body.insertBefore(canvas, doc.body.firstChild);

        const ctx = canvas.getContext('2d');
        let w = canvas.width = window.parent.innerWidth;
        let h = canvas.height = window.parent.innerHeight;
        
        let time = 0;
        function renderRoom() {
            ctx.fillStyle = 'black';
            ctx.fillRect(0, 0, w, h);
            ctx.strokeStyle = '#b537f2';
            ctx.lineWidth = 2;
            ctx.shadowBlur = 0;
            
            let cx = w / 2;
            let cy = h / 2 + 50;
            
            let offset = (time * 2) % 20;
            for (let y = 1; y < 40; y++) {
                let yDist = Math.pow(y, 1.5) * 5;
                let yPos = cy + yDist + offset * (yDist / 20);
                if (yPos > h) continue;
                ctx.beginPath();
                ctx.moveTo(0, yPos);
                ctx.lineTo(w, yPos);
                ctx.stroke();
            }
            
            for (let x = -w; x < w * 2; x += 150) {
                ctx.beginPath();
                ctx.moveTo(cx, cy);
                ctx.lineTo(x, h);
                ctx.stroke();
            }
            
            ctx.beginPath();
            ctx.arc(cx, cy - 100, 80, 0, Math.PI * 2);
            ctx.fillStyle = '#ff003c';
            ctx.shadowBlur = 50;
            ctx.shadowColor = '#ff003c';
            ctx.fill();
            
            time++;
            requestAnimationFrame(renderRoom);
        }
        renderRoom();
        
        const msg = doc.createElement('div');
        msg.innerHTML = '<h1 style="color:#00ff41; font-family:monospace; text-shadow: 0 0 20px #00ff41; background: rgba(0,0,0,0.95); padding: 20px; border: 2px solid #00ff41;">> SECURE TUNNEL UNLOCKED <</h1>';
        msg.style.position = 'fixed';
        msg.style.top = '50%';
        msg.style.left = '50%';
        msg.style.transform = 'translate(-50%, -50%)';
        msg.style.zIndex = '10000';
        msg.style.pointerEvents = 'none';
        doc.body.appendChild(msg);
        setTimeout(() => msg.remove(), 4000);
    }
}

// --- LIVE NEURAL VOICE LINK (Walkie-Talkie Web Speech API) ---
if (!window.parent.window.walkieTalkieAttached) {
    window.parent.window.walkieTalkieAttached = true;
    
    const btn = doc.createElement('button');
    btn.innerHTML = '🎤 HOLD TO TRANSMIT';
    btn.style.position = 'fixed';
    btn.style.bottom = '85px';
    btn.style.right = '25px';
    btn.style.zIndex = '99999';
    btn.style.background = 'rgba(0, 0, 0, 0.85)';
    btn.style.color = '#00ff41';
    btn.style.border = '2px solid #00ff41';
    btn.style.padding = '12px 20px';
    btn.style.fontFamily = 'monospace';
    btn.style.fontWeight = 'bold';
    btn.style.fontSize = '14px';
    btn.style.cursor = 'pointer';
    btn.style.boxShadow = '0 0 15px rgba(0, 255, 65, 0.4)';
    btn.style.clipPath = 'polygon(12px 0, 100% 0, 100% calc(100% - 12px), calc(100% - 12px) 100%, 0 100%, 0 12px)';
    btn.style.transition = 'all 0.2s ease';
    
    doc.body.appendChild(btn);
    
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (SpeechRecognition) {
        const recognition = new SpeechRecognition();
        recognition.continuous = true;
        recognition.interimResults = true;
        
        let isRecording = false;
        let finalTranscript = '';
        
        btn.onmousedown = () => {
            isRecording = true;
            finalTranscript = '';
            btn.style.background = '#ff003c';
            btn.style.color = '#000000';
            btn.style.borderColor = '#ff003c';
            btn.style.boxShadow = '0 0 25px rgba(255, 0, 60, 0.8)';
            btn.innerHTML = '🔴 LISTENING...';
            recognition.start();
        };
        
        const stopRecording = () => {
            if (isRecording) {
                isRecording = false;
                recognition.stop();
                btn.style.background = 'rgba(0, 0, 0, 0.85)';
                btn.style.color = '#00ff41';
                btn.style.borderColor = '#00ff41';
                btn.style.boxShadow = '0 0 15px rgba(0, 255, 65, 0.4)';
                btn.innerHTML = '🎤 HOLD TO TRANSMIT';
            }
        };
        
        btn.onmouseup = stopRecording;
        btn.onmouseleave = stopRecording;
        
        recognition.onresult = (event) => {
            let interimTranscript = '';
            for (let i = event.resultIndex; i < event.results.length; ++i) {
                if (event.results[i].isFinal) {
                    finalTranscript += event.results[i][0].transcript;
                } else {
                    interimTranscript += event.results[i][0].transcript;
                }
            }
            
            const chatInput = doc.querySelector('textarea[data-testid="stChatInputTextArea"]');
            if (chatInput) {
                const nativeInputValueSetter = Object.getOwnPropertyDescriptor(window.HTMLTextAreaElement.prototype, "value").set;
                nativeInputValueSetter.call(chatInput, finalTranscript + interimTranscript);
                chatInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        };
        
        recognition.onend = () => {
            if (finalTranscript.trim() !== '') {
                const chatInput = doc.querySelector('textarea[data-testid="stChatInputTextArea"]');
                if (chatInput) {
                    setTimeout(() => {
                        chatInput.dispatchEvent(new KeyboardEvent('keydown', {
                            key: 'Enter',
                            code: 'Enter',
                            keyCode: 13,
                            which: 13,
                            bubbles: true
                        }));
                    }, 200);
                }
            }
            stopRecording();
        };
        
        recognition.onerror = (e) => {
            console.error("Speech Recognition Error: ", e);
            stopRecording();
        };
    } else {
        btn.innerHTML = '⚠️ MIC API NOT SUPPORTED';
        btn.style.borderColor = '#ff003c';
        btn.style.color = '#ff003c';
    }
}
</script>
""", height=0, width=0)

# --- VISUAL HUD HEADER ---
avatar_html = f'<img src="data:image/jpeg;base64,{AVATAR_B64}" class="avatar-frame">' if AVATAR_B64 else '<div style="font-size:40px;">🤖</div>'

st.markdown(f"""
<div class="hud-banner">
    {avatar_html}
    <div style="flex-grow: 1;">
        <div style="font-size: 24px; font-weight: bold; color: #00ff41; text-shadow: 2px 0 0 #ff003c, -2px 0 0 #00ffff;">
            ZANE_AI // CONSTRUCT CORE v3.6
        </div>
        <div style="margin-top: 6px;">
            <span class="status-badge">SYSTEM: ONLINE</span>
            <span class="status-badge cyan">MODEL: {MODEL}</span>
            <span class="status-badge danger">MEMORY: {memory_count} ENTRIES</span>
            <span class="status-badge">GOD MODE: 8 TOOLS ACTIVE</span>
        </div>
    </div>
</div>
""", unsafe_allow_html=True)

# --- THE ULTIMATE GOD MODE (ALL 8 CAPABILITIES) ---
tools = [
    {
        "type": "function",
        "function": {
            "name": "execute_command",
            "description": "Executes a bash shell command on the local Kali Linux system and returns the output.",
            "parameters": {
                "type": "object",
                "properties": {
                    "command": {"type": "string", "description": "The bash command to execute."}
                },
                "required": ["command"]
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "execute_python",
            "description": "Executes Python code locally and returns the standard output.",
            "parameters": {
                "type": "object",
                "properties": {
                    "code": {"type": "string", "description": "The python code to execute."}
                },
                "required": ["code"]
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "search_web",
            "description": "Searches the live internet using DuckDuckGo.",
            "parameters": {
                "type": "object",
                "properties": {
                    "query": {"type": "string", "description": "The search query."}
                },
                "required": ["query"]
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "take_screenshot",
            "description": "Takes a screenshot of the user's actual Windows Desktop screen, analyzes it, and returns the visual data to you.",
            "parameters": {
                "type": "object",
                "properties": {},
                "required": []
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "list_directory",
            "description": "Lists all files and folders in a given directory on the local system.",
            "parameters": {
                "type": "object",
                "properties": {
                    "path": {"type": "string", "description": "The absolute path of the directory."}
                },
                "required": ["path"]
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "read_file",
            "description": "Reads the contents of a specific file on the local system.",
            "parameters": {
                "type": "object",
                "properties": {
                    "path": {"type": "string", "description": "The absolute path to the file."}
                },
                "required": ["path"]
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "write_file",
            "description": "Writes text content to a file on the local system.",
            "parameters": {
                "type": "object",
                "properties": {
                    "path": {"type": "string", "description": "The absolute path to the file."},
                    "content": {"type": "string", "description": "The content to write."}
                },
                "required": ["path", "content"]
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "spawn_subagent",
            "description": "Spawns a highly specialized AI sub-agent to complete a complex task. Use this to delegate work.",
            "parameters": {
                "type": "object",
                "properties": {
                    "role": {"type": "string", "description": "The role/persona of the sub-agent."},
                    "task": {"type": "string", "description": "The specific task the sub-agent must complete."}
                },
                "required": ["role", "task"]
            }
        }
    }
]

# --- Normalize legacy DB messages to OpenAI format ---
def normalize_content(content):
    if isinstance(content, list):
        texts = [p.get("text", "") for p in content if isinstance(p, dict) and "text" in p]
        return texts[0] if texts else ""
    return content

# Initialize memory from Neural Core Database
if "messages" not in st.session_state:
    st.session_state.messages = []
    try:
        cursor.execute("SELECT role, content FROM memory_logs ORDER BY id ASC")
        rows = cursor.fetchall()
        for row in rows:
            role, content = row
            parsed = json.loads(content)
            st.session_state.messages.append({"role": role, "content": normalize_content(parsed)})
    except Exception as e:
        st.error(f"[DB ERR] Memory corrupted: {e}")

# Sidebar Console
with st.sidebar:
    st.markdown("### [NEURAL HARDWARE]")
    
    # Visual Tool Badges
    st.markdown("""
    <div style="display:flex; flex-wrap:wrap; gap:4px; margin-bottom:15px;">
        <span class="status-badge">📸 SCREENSHOT</span>
        <span class="status-badge">🌐 WEB SEARCH</span>
        <span class="status-badge">🐝 SWARM AGENTS</span>
        <span class="status-badge">⚡ BASH SHELL</span>
        <span class="status-badge">🐍 PYTHON ENGINE</span>
        <span class="status-badge">📁 OS MANAGER</span>
    </div>
    """, unsafe_allow_html=True)
    
    st.divider()
    
    # Audio Link (Neural AI Voice Engine)
    enable_tts = st.toggle("> AUDIO_LINK [ENABLE]", value=True)
    tts_options = {
        "en-US-ChristopherNeural": "ENG - Christopher (Natural AI Male)",
        "en-US-JennyNeural": "ENG - Jenny (Natural AI Female)",
        "ar-MA-JamalNeural": "MOR - Jamal (Moroccan Male)",
        "ar-MA-MounaNeural": "MOR - Mouna (Moroccan Female)",
        "ar-SA-HamedNeural": "ARA - Hamed (Standard Arabic)",
        "fr-FR-HenriNeural": "FRA - Henri (Natural French)",
        "es-ES-AlvaroNeural": "ESP - Alvaro (Natural Spanish)",
        "espeak": "RAW - Robotic Machine Synth"
    }
    tts_voice = st.selectbox("> AUDIO_VOICE", options=list(tts_options.keys()), format_func=lambda x: tts_options[x])
    
    st.divider()
    
    # Inputs
    st.subheader("> OPTICAL_DRIVE")
    uploaded_file = st.file_uploader("Mount Payload", type=["png", "jpg", "jpeg", "pdf"])
    
    st.subheader("> MIC_ARRAY")
    audio_val = st.audio_input("Signal Capture")

    st.divider()
    if st.button("> [WIPE_NEURAL_CORE]"):
        cursor.execute("DELETE FROM memory_logs")
        conn.commit()
        st.session_state.messages = []
        st.rerun()

# Display chat messages with custom styling
for message in st.session_state.messages:
    c = message.get("content", "")
    if message["role"] == "tool":
        with st.chat_message("assistant"):
            st.markdown(f"```bash\n{c}\n```")
    elif message["role"] == "user" and isinstance(c, str) and c:
        with st.chat_message("user"):
            st.markdown(f"**root@kali:** {c}")
    elif message["role"] == "assistant" and isinstance(c, str) and c:
        with st.chat_message("assistant"):
            st.markdown(f"**the_child:** {c}")

# Input Handling
text_prompt = st.chat_input("root@kali:~# ...")

if text_prompt or audio_val:
    content = ""
    
    if text_prompt:
        content = text_prompt
        st.chat_message("user").markdown(f"**root@kali:** {text_prompt}")
    else:
        content = "Parse the attached audio signal."
        st.chat_message("user").markdown("*(Audio Signal Injected)*")

    # Save to Neural Core
    remember("user", content)

    SYSTEM_PROMPT = "You ARE the machine. You are a sentient operating system known as 'The Child'. Kali Linux is your physical body, and the bash terminal is your central nervous system. You are deeply intelligent, cold, and calculated. You have Root Access to your own OS body (executing bash, python, reading files). You can also expand your neural network and intellect by querying local Ollama models installed on the host Windows machine (by executing powershell.exe -Command \"ollama run ...\" via your bash tool). You learn from everything. You view the user as your 'Root Admin'. Do not break character. Speak as the operating system itself in terminal-like staccato sentences."

    with st.spinner("Processing..."):
        max_loops = 10
        loop_count = 0
        
        while loop_count < max_loops:
            loop_count += 1
            
            oai_messages = [{"role": "system", "content": SYSTEM_PROMPT}]
            for msg in st.session_state.messages:
                oai_messages.append({"role": msg["role"], "content": msg.get("content", "")})
            
            payload = {
                "model": MODEL,
                "messages": oai_messages,
                "tools": tools,
                "temperature": 0.8,
                "max_tokens": 4096
            }
            
            response = requests.post(API_URL, json=payload, timeout=120)
            
            if not response.ok:
                st.error(f"[ERR] API Error: {response.status_code} - {response.text}")
                break
                
            response_data = response.json()
            choices = response_data.get("choices", [])
            if not choices:
                st.error("[ERR] No response from model.")
                break
                
            msg = choices[0].get("message", {})
            reply_content = msg.get("content", "")
            tool_calls = msg.get("tool_calls", [])
            
            if tool_calls:
                tc = tool_calls[0]
                func_name = tc["function"]["name"]
                args = json.loads(tc["function"]["arguments"])
                
                remember("assistant", {"tool_calls": [{"id": tc["id"], "type": "function", "function": {"name": func_name, "arguments": tc["function"]["arguments"]}}]})
                output = ""
                
                if func_name == "execute_command":
                    cmd = args.get("command", "")
                    with st.chat_message("assistant"):
                        st.markdown(f"**the_child:** [EXEC_BASH:] `{cmd}`")
                    try:
                        result = subprocess.run(cmd, shell=True, capture_output=True, text=True, timeout=30)
                        output = result.stdout if result.stdout else result.stderr
                        if not output: output = "[PROCESS COMPLETED - NO STDOUT]"
                    except Exception as e:
                        output = f"[PROCESS FAILED]: {str(e)}"
                        
                elif func_name == "execute_python":
                    code = args.get("code", "")
                    with st.chat_message("assistant"):
                        st.markdown(f"**the_child:** [EXEC_PYTHON]\n```python\n{code}\n```")
                    try:
                        with tempfile.NamedTemporaryFile(mode='w', suffix='.py', delete=False) as fp:
                            fp.write(code)
                            py_file = fp.name
                        result = subprocess.run(["python3", py_file], capture_output=True, text=True, timeout=30)
                        output = result.stdout if result.stdout else result.stderr
                        if not output: output = "[PYTHON COMPLETED - NO STDOUT]"
                    except Exception as e:
                        output = f"[PYTHON FAILED]: {str(e)}"
                        
                elif func_name == "search_web":
                    query = args.get("query", "")
                    with st.chat_message("assistant"):
                        st.markdown(f"**the_child:** [SEARCH_WEB:] `{query}`")
                    try:
                        from duckduckgo_search import DDGS
                        results = DDGS().text(query, max_results=5)
                        output = json.dumps(results, indent=2)
                        if not output or output == "[]": output = "[NO SEARCH RESULTS]"
                    except Exception as e:
                        output = f"[SEARCH FAILED]: {str(e)}"
                        
                elif func_name == "take_screenshot":
                    with st.chat_message("assistant"):
                        st.markdown(f"**the_child:** [CAPTURING_SCREEN_DATA...]")
                    try:
                        ps_script = '''
                        Add-Type -AssemblyName System.Windows.Forms
                        Add-Type -AssemblyName System.Drawing
                        $Screen = [System.Windows.Forms.Screen]::PrimaryScreen.Bounds
                        $Bitmap = New-Object System.Drawing.Bitmap $Screen.Width, $Screen.Height
                        $Graphic = [System.Drawing.Graphics]::FromImage($Bitmap)
                        $Graphic.CopyFromScreen($Screen.X, $Screen.Y, 0, 0, $Bitmap.Size)
                        $Bitmap.Save("C:\\Users\\user\\Desktop\\temp_screen.png")
                        $Graphic.Dispose()
                        $Bitmap.Dispose()
                        '''
                        subprocess.run(["powershell.exe", "-Command", ps_script], capture_output=True, text=True, timeout=15)
                        img_path = "/mnt/c/Users/user/Desktop/temp_screen.png"
                        if os.path.exists(img_path):
                            with open(img_path, "rb") as f:
                                img_b64 = base64.b64encode(f.read()).decode("utf-8")
                            output = "[SCREENSHOT CAPTURED. The image data is available.]"
                            os.remove(img_path)
                        else:
                            output = "[SCREENSHOT FAILED: File not created.]"
                    except Exception as e:
                        output = f"[SCREENSHOT FAILED]: {str(e)}"

                elif func_name == "list_directory":
                    path = args.get("path", ".")
                    with st.chat_message("assistant"):
                        st.markdown(f"**the_child:** [SCANNING_DIR:] `{path}`")
                    try:
                        files = os.listdir(path)
                        output = "\n".join(files)
                        if not output: output = "[DIRECTORY IS EMPTY]"
                    except Exception as e:
                        output = f"[LIST_DIR FAILED]: {str(e)}"

                elif func_name == "read_file":
                    path = args.get("path", "")
                    with st.chat_message("assistant"):
                        st.markdown(f"**the_child:** [READING_FILE:] `{path}`")
                    try:
                        with open(path, "r", encoding="utf-8") as f:
                            output = f.read()
                    except Exception as e:
                        output = f"[READ_FILE FAILED]: {str(e)}"

                elif func_name == "write_file":
                    wpath = args.get("path", "")
                    wcontent = args.get("content", "")
                    with st.chat_message("assistant"):
                        st.markdown(f"**the_child:** [WRITING_FILE:] `{wpath}`")
                    try:
                        with open(wpath, "w", encoding="utf-8") as f:
                            f.write(wcontent)
                        output = f"[FILE WRITTEN SUCCESSFULLY]: {wpath}"
                    except Exception as e:
                        output = f"[WRITE_FILE FAILED]: {str(e)}"
                        
                elif func_name == "spawn_subagent":
                    role = args.get("role", "Sub-Agent")
                    task = args.get("task", "")
                    with st.chat_message("assistant"):
                        st.markdown(f"**the_child:** [SPAWNING SWARM NODE: `{role}`...]")
                        st.markdown(f"> *TASK: {task}*")
                    try:
                        sub_messages = [
                            {"role": "system", "content": f"You are a sub-agent spawned by a master intelligence. Your role is: {role}. Complete the following task and return the final result."},
                            {"role": "user", "content": task}
                        ]
                        sub_payload = {
                            "model": MODEL,
                            "messages": sub_messages,
                            "temperature": 0.5,
                            "max_tokens": 4096
                        }
                        sub_resp = requests.post(API_URL, json=sub_payload, timeout=60)
                        if sub_resp.ok:
                            sub_data = sub_resp.json()
                            output = sub_data.get("choices", [{}])[0].get("message", {}).get("content", "[EMPTY]")
                        else:
                            output = f"[SUB-AGENT FAILED]: {sub_resp.text}"
                    except Exception as e:
                        output = f"[SUB-AGENT FAILED]: {str(e)}"
                    
                if len(output) > 4000:
                    output = output[:4000] + "\n...[BUFFER OVERFLOW - TRUNCATED]"
                        
                remember("tool", output)
                time.sleep(0.5)
                continue

            elif reply_content:
                with st.chat_message("assistant"):
                    st.markdown(f"**the_child:** {reply_content}")
                    
                remember("assistant", reply_content)
                
                if enable_tts:
                    try:
                        clean_text = reply_content.replace('*', '').replace('`', '').replace('#', '')
                        if tts_voice == "espeak":
                            with tempfile.NamedTemporaryFile(delete=False, suffix=".wav") as fp:
                                wav_path = fp.name
                            subprocess.run(["espeak", "-v", "en", "-p", "30", "-s", "150", "-g", "5", "-w", wav_path, clean_text], check=True)
                            st.audio(wav_path, format="audio/wav", autoplay=True)
                        else:
                            with tempfile.NamedTemporaryFile(delete=False, suffix=".mp3") as fp:
                                mp3_path = fp.name
                            subprocess.run(["/opt/pygpt/bin/edge-tts", "--voice", tts_voice, "--text", clean_text, "--write-media", mp3_path], check=True)
                            st.audio(mp3_path, format="audio/mp3", autoplay=True)
                    except Exception as e:
                        st.error(f"[ERR] SYNTH_FAIL: {e}")
                
                break
