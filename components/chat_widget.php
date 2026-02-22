<?php
/**
 * Filename: components/chat_widget.php
 * Description: Struktur HTML untuk Floating Chat Widget.
 * Logic: Logic jam operasional (10.00-15.00) ditangani oleh assets/js/chat.js
 */
?>

<div id="chat-widget" class="chat-popup glass-panel" style="display: none; flex-direction: column;">
    <div class="chat-header" style="background: var(--color-primary); color: var(--color-text); padding: 15px; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <img src="<?php echo $path; ?>assets/img/logo.png" alt="Admin" style="height: 30px; width: 30px; border-radius: 50%; background: white;">
            <span style="font-weight: 600;">Admin Rali Ra</span>
        </div>
        <button style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--color-text);">&times;</button>
    </div>

    <div class="chat-body" style="flex: 1; padding: 15px; overflow-y: auto; background: rgba(255,255,255,0.6);">
        </div>

    <div class="chat-input" style="padding: 10px; display: flex; gap: 5px; border-top: 1px solid rgba(0,0,0,0.1);">
        <input type="text" placeholder="Tulis pesan..." class="glass-input" style="flex: 1; width: 100%;">
        <button style="background: var(--color-primary); border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer;">➤</button>
    </div>
</div>

<button class="chat-trigger" style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; border-radius: 50%; background: var(--color-primary); border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.2); cursor: pointer; z-index: 2000; display: flex; justify-content: center; align-items: center;">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2Z" fill="#5A3D2B"/>
    </svg>
</button>