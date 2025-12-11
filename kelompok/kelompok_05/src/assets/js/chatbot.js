/**
 * LampungSmart Chatbot - Rule-based FAQ Assistant
 * Simple chatbot dengan 5 pertanyaan umum pre-defined
 */

class LampungSmartChatbot {
    constructor() {
        this.isOpen = false;
        this.knowledgeBase = [
            {
                keywords: ['daftar', 'registrasi', 'sign up', 'akun', 'mendaftar'],
                question: 'Cara daftar',
                answer: 'Untuk mendaftar di LampungSmart:<br>1. Klik tombol "Daftar" di pojok kanan atas<br>2. Isi formulir dengan data lengkap (Nama, Email, Password)<br>3. Klik "Daftar Sekarang"<br>4. Login dengan akun yang sudah dibuat'
            },
            {
                keywords: ['pengaduan', 'lapor', 'complaint', 'syarat laporan'],
                question: 'Syarat pengaduan',
                answer: 'Syarat membuat pengaduan:<br>1. Sudah memiliki akun dan login<br>2. Isi judul pengaduan dengan jelas<br>3. Tulis deskripsi detail masalah yang dilaporkan<br>4. Cantumkan lokasi kejadian<br>5. Upload foto bukti (opsional tapi direkomendasikan)'
            },
            {
                keywords: ['umkm', 'usaha', 'perizinan', 'daftar umkm'],
                question: 'Cara daftar UMKM',
                answer: 'Untuk mendaftar UMKM:<br>1. Login ke akun Anda<br>2. Buka menu Dashboard<br>3. Pilih "Daftar UMKM"<br>4. Isi formulir dengan data usaha (Nama Usaha, Bidang, Alamat, No. Telepon)<br>5. Submit dan tunggu approval dari admin'
            },
            {
                keywords: ['status', 'cek', 'tracking', 'progres', 'laporan saya'],
                question: 'Cek status pengaduan',
                answer: 'Cara cek status pengaduan:<br>1. Login ke dashboard Anda<br>2. Klik menu "Riwayat Pengaduan"<br>3. Lihat daftar semua pengaduan dengan status:<br>- Pending (menunggu review)<br>- Proses (sedang ditangani)<br>- Selesai (sudah ditangani)<br>- Ditolak (tidak memenuhi syarat)'
            },
            {
                keywords: ['kontak', 'hubungi', 'contact', 'admin', 'bantuan', 'help'],
                question: 'Hubungi admin',
                answer: 'Cara menghubungi admin:<br>1. Buka halaman "Hubungi Kami" di menu navigasi<br>2. Isi formulir kontak dengan lengkap<br>3. Tulis pesan/pertanyaan Anda<br>4. Admin akan merespon via email maksimal 1x24 jam<br><br>Atau hubungi langsung:<br>📞 (0721) 123-4567<br>📧 info@lampungsmart.go.id'
            }
        ];
        
        this.init();
    }
    
    init() {
        this.createChatWidget();
        this.attachEventListeners();
    }
    
    createChatWidget() {
        const chatHTML = `
            <div id="lampung-chatbot" class="lampung-chatbot">
                <!-- Chat Toggle Button -->
                <button id="chat-toggle-btn" class="chat-toggle-btn" aria-label="Toggle chatbot">
                    <i class="bi bi-chat-dots-fill"></i>
                    <span class="chat-badge">!</span>
                </button>
                
                <!-- Chat Window -->
                <div id="chat-window" class="chat-window">
                    <div class="chat-header">
                        <div class="d-flex align-items-center">
                            <img src="../assets/images/logo-lampung.png" alt="Logo" class="chat-logo">
                            <div>
                                <h6 class="mb-0">LampungSmart Bot</h6>
                                <small class="text-white-50">Online</small>
                            </div>
                        </div>
                        <button id="chat-close-btn" class="chat-close-btn" aria-label="Close chatbot">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    
                    <div class="chat-body" id="chat-body">
                        <div class="chat-message bot-message">
                            <div class="message-content">
                                Halo! 👋 Saya asisten virtual LampungSmart.<br><br>
                                Pilih pertanyaan umum atau ketik pertanyaan Anda:
                            </div>
                            <small class="message-time">${this.getCurrentTime()}</small>
                        </div>
                        
                        <div class="quick-questions">
                            ${this.knowledgeBase.map((item, index) => 
                                `<button class="quick-question-btn" data-index="${index}">
                                    ${item.question}
                                </button>`
                            ).join('')}
                        </div>
                    </div>
                    
                    <div class="chat-footer">
                        <input type="text" id="chat-input" class="chat-input" 
                               placeholder="Ketik pertanyaan..." autocomplete="off">
                        <button id="chat-send-btn" class="chat-send-btn" aria-label="Send message">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', chatHTML);
    }
    
    attachEventListeners() {
        const toggleBtn = document.getElementById('chat-toggle-btn');
        const closeBtn = document.getElementById('chat-close-btn');
        const sendBtn = document.getElementById('chat-send-btn');
        const input = document.getElementById('chat-input');
        const quickBtns = document.querySelectorAll('.quick-question-btn');
        
        toggleBtn.addEventListener('click', () => this.toggleChat());
        closeBtn.addEventListener('click', () => this.toggleChat());
        sendBtn.addEventListener('click', () => this.handleUserMessage());
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.handleUserMessage();
        });
        
        quickBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.currentTarget.dataset.index);
                this.handleQuickQuestion(index);
            });
        });
    }
    
    toggleChat() {
        this.isOpen = !this.isOpen;
        const chatWindow = document.getElementById('chat-window');
        const toggleBtn = document.getElementById('chat-toggle-btn');
        const badge = toggleBtn.querySelector('.chat-badge');
        
        if (this.isOpen) {
            chatWindow.classList.add('active');
            toggleBtn.classList.add('active');
            if (badge) badge.style.display = 'none';
        } else {
            chatWindow.classList.remove('active');
            toggleBtn.classList.remove('active');
        }
    }
    
    handleQuickQuestion(index) {
        const item = this.knowledgeBase[index];
        this.addUserMessage(item.question);
        setTimeout(() => {
            this.addBotMessage(item.answer);
        }, 500);
    }
    
    handleUserMessage() {
        const input = document.getElementById('chat-input');
        const message = input.value.trim();
        
        if (!message) return;
        
        this.addUserMessage(message);
        input.value = '';
        
        // Cari jawaban di knowledge base
        setTimeout(() => {
            const answer = this.findAnswer(message);
            this.addBotMessage(answer);
        }, 500);
    }
    
    findAnswer(userMessage) {
        const lowerMessage = userMessage.toLowerCase();
        
        // Cari match di knowledge base
        for (const item of this.knowledgeBase) {
            for (const keyword of item.keywords) {
                if (lowerMessage.includes(keyword)) {
                    return item.answer;
                }
            }
        }
        
        // Tidak ditemukan - arahkan ke form kontak
        return `Maaf, saya belum bisa menjawab pertanyaan tersebut. 😔<br><br>
                Silakan hubungi admin kami melalui:<br>
                📧 <a href="hubungi-kami.php" class="text-white">Form Kontak</a><br>
                📞 (0721) 123-4567<br><br>
                Atau coba pertanyaan lain dari menu di atas.`;
    }
    
    addUserMessage(message) {
        const chatBody = document.getElementById('chat-body');
        const messageHTML = `
            <div class="chat-message user-message">
                <div class="message-content">${this.escapeHtml(message)}</div>
                <small class="message-time">${this.getCurrentTime()}</small>
            </div>
        `;
        chatBody.insertAdjacentHTML('beforeend', messageHTML);
        this.scrollToBottom();
    }
    
    addBotMessage(message) {
        const chatBody = document.getElementById('chat-body');
        const messageHTML = `
            <div class="chat-message bot-message">
                <div class="message-content">${message}</div>
                <small class="message-time">${this.getCurrentTime()}</small>
            </div>
        `;
        chatBody.insertAdjacentHTML('beforeend', messageHTML);
        this.scrollToBottom();
    }
    
    scrollToBottom() {
        const chatBody = document.getElementById('chat-body');
        chatBody.scrollTop = chatBody.scrollHeight;
    }
    
    getCurrentTime() {
        const now = new Date();
        return now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }
    
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
}

// Initialize chatbot on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.lampungChatbot = new LampungSmartChatbot();
    });
} else {
    window.lampungChatbot = new LampungSmartChatbot();
}
