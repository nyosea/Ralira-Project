<?php
// components/footer.php
?>
    <footer class="bg-[#5A3D2B] text-[#F4EED8] pt-16 pb-8 mt-12 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full mix-blend-overlay filter blur-3xl"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-12 mb-12">
                
                <div class="md:col-span-5 space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-ralira-primary rounded-full flex items-center justify-center text-[#5A3D2B] shadow-lg">
                            <img src="<?php echo $path; ?>assets/img/logo.png" class="w-6 h-6 object-contain" alt="Logo">
                        </div>
                        <div>
                            <span class="font-bold text-2xl tracking-wide">Rali Ra</span>
                            <p class="text-[0.6rem] text-ralira-primary uppercase tracking-widest -mt-1 opacity-80">Biro Psikologi</p>
                        </div>
                    </div>
                    <p class="text-sm opacity-80 leading-relaxed max-w-sm">
                        Terbit dari Timur. Biro Psikologi yang berkomitmen menemani perjalanan kesehatan mental Anda dengan pendekatan personal, profesional, dan penuh empati.
                    </p>
                    <div class="flex gap-4 pt-2">
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-ralira-primary hover:text-[#5A3D2B] transition">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-ralira-primary hover:text-[#5A3D2B] transition">
                            <i class="fa-brands fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-ralira-primary hover:text-[#5A3D2B] transition">
                            <i class="fa-brands fa-tiktok"></i>
                        </a>
                    </div>
                </div>

                <div class="md:col-span-3">
                    <h4 class="font-bold text-lg mb-6 text-ralira-primary border-b border-white/10 pb-2 inline-block">Navigasi</h4>
                    <ul class="space-y-3 text-sm opacity-80">
                        <li><a href="<?php echo $path; ?>index.php" class="hover:text-ralira-primary hover:translate-x-1 transition inline-block">Beranda</a></li>
                        <li><a href="<?php echo $path; ?>pages/public/services.php" class="hover:text-ralira-primary hover:translate-x-1 transition inline-block">Layanan & Harga</a></li>
                        <li><a href="<?php echo $path; ?>pages/public/about.php" class="hover:text-ralira-primary hover:translate-x-1 transition inline-block">Tentang Kami</a></li>
                        <li><a href="<?php echo $path; ?>pages/public/articles.php" class="hover:text-ralira-primary hover:translate-x-1 transition inline-block">Artikel</a></li>
                        <li><a href="<?php echo $path; ?>pages/auth/login.php" class="hover:text-ralira-primary hover:translate-x-1 transition inline-block font-bold">Area Klien</a></li>
                    </ul>
                </div>

                <div class="md:col-span-4">
                    <h4 class="font-bold text-lg mb-6 text-ralira-primary border-b border-white/10 pb-2 inline-block">Hubungi Kami</h4>
                    <ul class="space-y-4 text-sm opacity-80">
                        <li class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-location-dot text-ralira-primary"></i>
                            </div>
                            <span class="mt-1">Jl. Sentani Harmoni No. 12, Jayapura, Papua, Indonesia.</span>
                        </li>
                        <li class="flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0">
                                <i class="fa-brands fa-whatsapp text-ralira-primary"></i>
                            </div>
                            <span>+62 812-9360-5651 (WhatsApp Only)</span>
                        </li>
                        <li class="flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-envelope text-ralira-primary"></i>
                            </div>
                            <span>admin@ralira.id</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-white/10 pt-8 text-center flex flex-col md:flex-row justify-between items-center text-xs opacity-60 gap-4">
                <p>&copy; <?php echo date("Y"); ?> Biro Psikologi Rali Ra. All rights reserved.</p>
                <div class="flex gap-6">
                    <a href="#" class="hover:text-white">Privacy Policy</a>
                    <a href="#" class="hover:text-white">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <button onclick="openWhatsApp()" class="fixed bottom-6 right-6 w-16 h-16 bg-green-500 text-white rounded-full shadow-2xl flex items-center justify-center text-3xl hover:scale-110 hover:rotate-12 transition z-50 animate-bounce hover:animate-none group">
        <i class="fa-brands fa-whatsapp group-hover:hidden"></i>
        <i class="fa-solid fa-xmark hidden group-hover:block"></i>
    </button>

    <div id="chat-box" class="fixed bottom-24 right-6 w-80 sm:w-96 bg-white/80 backdrop-blur-xl rounded-2xl shadow-2xl transform scale-0 origin-bottom-right transition-transform duration-300 z-50 overflow-hidden flex flex-col border border-white/50">
        <div class="bg-gradient-to-r from-green-500 to-green-600 p-4 text-white flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center border border-white/30">
                    <i class="fa-brands fa-whatsapp text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm">Admin Rali Ra</h4>
                    <p class="text-xs opacity-90 flex items-center gap-1"><span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span> Online di WhatsApp</p>
                </div>
            </div>
            <button onclick="toggleChat()" class="text-white/80 hover:text-white hover:bg-white/10 rounded-full p-1 transition">
                <i class="fa-solid fa-minus"></i>
            </button>
        </div>
        
        <div class="p-4 flex-1 overflow-y-auto bg-white/50 h-72 space-y-4">
            <p class="text-center text-xs text-gray-400 my-2">Hari ini</p>
            <div class="flex gap-3">
                <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white text-xs flex-shrink-0">
                    <i class="fa-brands fa-whatsapp"></i>
                </div>
                <div class="bg-white p-3 rounded-2xl rounded-tl-none shadow-sm text-sm text-ralira-text border border-gray-100">
                    Halo! Selamat datang di Rali Ra. Ada yang bisa kami bantu mengenai jadwal konsultasi atau psikotes? Silakan klik tombol di bawah untuk chat langsung via WhatsApp! 
                </div>
            </div>
            
            <div class="text-center py-2">
                <button onclick="openWhatsApp()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-full text-sm font-semibold transition transform hover:scale-105 shadow-lg">
                    <i class="fa-brands fa-whatsapp mr-2"></i>Chat via WhatsApp
                </button>
            </div>
        </div>

        <div class="p-3 bg-white/80 border-t border-white">
            <div class="text-center">
                <p class="text-xs text-gray-600 mb-2">Atau klik tombol WhatsApp di bawah:</p>
                <button onclick="openWhatsApp()" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-full text-sm font-semibold transition transform hover:scale-105 shadow-lg w-full">
                    <i class="fa-brands fa-whatsapp mr-2"></i>+62 812-9360-5651
                </button>
            </div>
            <p class="text-[10px] text-center mt-2 text-gray-500">Jam Operasional: 10.00 - 15.00 WIB</p>
        </div>
    </div>

    <script>
        // 1. Mobile Menu Toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const search = document.getElementById('mobile-search');
            
            if(!search.classList.contains('hidden')) {
                search.classList.add('hidden');
            }
            menu.classList.toggle('hidden');
        }

        // 2. Mobile Search Toggle
        function toggleSearchMobile() {
            const search = document.getElementById('mobile-search');
            const menu = document.getElementById('mobile-menu');
            
            if(!menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
            search.classList.toggle('hidden');
        }

        // 3. Slider Logic (Horizontal Scroll)
        function scrollSlider(direction) {
            const slider = document.getElementById('service-slider');
            const scrollAmount = 350;

            if (direction === 'left') {
                slider.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            } else {
                slider.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            }
        }

        // 4. Chat Widget Toggle
        function toggleChat() {
            const chatBox = document.getElementById('chat-box');
            if (chatBox.classList.contains('scale-0')) {
                chatBox.classList.remove('scale-0');
                chatBox.classList.add('scale-100');
            } else {
                chatBox.classList.remove('scale-100');
                chatBox.classList.add('scale-0');
            }
        }

        // 5. WhatsApp Integration
        function openWhatsApp() {
            const phoneNumber = '+6281293605651'; // Admin WhatsApp number
            const message = encodeURIComponent('Halo Admin Rali Ra, saya ingin bertanya tentang layanan psikologi.');
            const whatsappUrl = `https://wa.me/${phoneNumber.replace(/[^\d]/g, '')}?text=${message}`;
            window.open(whatsappUrl, '_blank');
        }

        // 6. Navbar Blur Effect on Scroll
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('shadow-md', 'bg-white/60');
            } else {
                navbar.classList.remove('shadow-md', 'bg-white/60');
            }
        });
    </script>

    <!-- Global JavaScript -->
    <script src="<?php echo $path; ?>assets/js/script.js"></script>
</body>
</html>