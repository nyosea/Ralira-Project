/**
 * Filename: chat.js
 * Description: Logic Live Chat Pop-up dengan validasi jam operasional & simulasi Chatbot.
 * Requirement: Jam Operasional 10.00 - 15.00.
 */

document.addEventListener('DOMContentLoaded', () => {
    initChatWidget();
});

function initChatWidget() {
    const chatWidget = document.getElementById('chat-widget');
    const chatTrigger = document.querySelector('.chat-trigger');
    const closeBtn = document.querySelector('.chat-header button');
    const sendBtn = document.querySelector('.chat-input button');
    const inputField = document.querySelector('.chat-input input');
    const chatBody = document.querySelector('.chat-body');

    if (!chatWidget || !chatTrigger) return;

    // --- 1. Toggle Visibility ---
    const toggleChat = () => {
        if (chatWidget.style.display === 'flex') {
            chatWidget.style.display = 'none';
        } else {
            chatWidget.style.display = 'flex';
            // Fokus ke input saat dibuka
            setTimeout(() => inputField.focus(), 300);
            checkOperationalHours(); // Cek jam setiap kali dibuka
        }
    };

    chatTrigger.addEventListener('click', toggleChat);
    closeBtn.addEventListener('click', toggleChat);

    // --- 2. Operational Hours Logic (10.00 - 15.00) ---
    function checkOperationalHours() {
        const now = new Date();
        const hour = now.getHours();
        const isOperational = hour >= 10 && hour < 15;

        // Kosongkan chat body jika baru dibuka pertama kali (opsional)
        if (chatBody.children.length <= 1) {
            const systemMsg = document.createElement('p');
            systemMsg.style.fontSize = '0.8rem';
            systemMsg.style.textAlign = 'center';
            systemMsg.style.color = '#888';
            systemMsg.style.marginBottom = '10px';

            if (isOperational) {
                systemMsg.textContent = "Admin Online. Silakan hubungi kami.";
            } else {
                systemMsg.textContent = "Admin Offline (10.00-15.00). Anda terhubung dengan Chatbot.";
            }
            chatBody.prepend(systemMsg);
        }
        return isOperational;
    }

    // --- 3. Send Message & Chatbot Simulation ---
    const sendMessage = () => {
        const text = inputField.value.trim();
        if (text === "") return;

        // a. Tampilkan pesan User
        appendMessage('user', text);
        inputField.value = "";

        // b. Simulasi Respon (Admin vs Bot)
        const isOperational = checkOperationalHours();
        
        // Tampilkan indikator "Typing..."
        const typingIndicator = document.createElement('div');
        typingIndicator.textContent = "Sedang mengetik...";
        typingIndicator.className = "typing-indicator";
        typingIndicator.style.fontSize = "0.75rem";
        typingIndicator.style.marginLeft = "10px";
        chatBody.appendChild(typingIndicator);
        chatBody.scrollTop = chatBody.scrollHeight;

        setTimeout(() => {
            chatBody.removeChild(typingIndicator); // Hapus indikator
            
            if (isOperational) {
                // Simulasi Admin (Di real app ini via WebSocket/Database)
                appendMessage('admin', "Halo, admin Rali Ra di sini. Mohon tunggu sebentar ya.");
            } else {
                // Logic Chatbot Sederhana
                let botReply = "Maaf, kami sedang tutup. Silakan tinggalkan pesan atau hubungi WhatsApp kami.";
                if (text.toLowerCase().includes('harga')) botReply = "Untuk informasi harga, silakan hubungi WhatsApp CP kami.";
                if (text.toLowerCase().includes('jadwal')) botReply = "Jadwal bisa dilihat di menu Dashboard setelah Login.";
                
                appendMessage('bot', botReply);
            }
        }, 1500); // Delay 1.5 detik
    };

    // Helper: Append Message ke UI
    function appendMessage(sender, message) {
        const msgDiv = document.createElement('div');
        msgDiv.textContent = message;
        msgDiv.style.padding = "8px 12px";
        msgDiv.style.borderRadius = "8px";
        msgDiv.style.marginBottom = "8px";
        msgDiv.style.maxWidth = "80%";
        msgDiv.style.fontSize = "0.9rem";

        if (sender === 'user') {
            msgDiv.style.background = "var(--color-primary)"; // Kuning
            msgDiv.style.color = "var(--color-text)";
            msgDiv.style.alignSelf = "flex-end"; // Rata kanan
            msgDiv.style.marginLeft = "auto";
        } else {
            msgDiv.style.background = "#fff"; // Putih
            msgDiv.style.border = "1px solid #ddd";
            msgDiv.style.alignSelf = "flex-start"; // Rata kiri
        }

        chatBody.appendChild(msgDiv);
        chatBody.scrollTop = chatBody.scrollHeight; // Auto scroll ke bawah
    }

    // Event Listener Kirim
    sendBtn.addEventListener('click', sendMessage);
    inputField.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });
}