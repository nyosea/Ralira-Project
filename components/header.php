<?php
// components/header.php
// Pastikan path didefinisikan untuk menghindari broken link
if (!isset($path)) $path = '../../';
if (!isset($page_title)) $page_title = 'Biro Psikologi Rali Ra';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="icon" href="<?php echo $path; ?>assets/img/logo.png" type="image/png">

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ralira: {
                            bg: '#F4EED8',       // Eggshell (Background Utama)
                            primary: '#FBBA00',  // Selective Yellow (Aksen Kuning)
                            accent: '#E5781E',   // Vivid Tangelo (Aksen Orange)
                            text: '#5A3D2B',     // Royal Brown (Teks Gelap)
                        }
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    }
                }
            }
        }

        // Mobile menu functions
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Pastikan semua kode JavaScript yang berinteraksi dengan DOM berada di dalam event listener ini
            // Contoh kode yang mungkin menyebabkan error
            if (document.body && document.body.classList.contains('some-class')) {
                console.log('Class exists');
            }
        });
    </script>
</head>
<body class="font-sans antialiased text-ralira-text bg-ralira-bg selection:bg-ralira-primary selection:text-white">

    <nav class="fixed w-full z-50 transition-all duration-300 top-0 glass-nav" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-24">
                
                <a href="<?php echo $path; ?>index.php" class="flex-shrink-0 flex items-center gap-3 group">
                    <div class="w-12 h-12 bg-white/80 rounded-full flex items-center justify-center shadow-lg group-hover:scale-110 transition duration-300">
                        <img src="<?php echo $path; ?>assets/img/logo.png" alt="Logo" class="w-8 h-8 object-contain">
                    </div>
                    <div class="flex flex-col">
                        <span class="font-bold text-2xl tracking-tight text-ralira-text leading-none">Rali Ra</span>
                        <span class="text-[0.65rem] text-ralira-accent uppercase font-bold tracking-widest mt-1">Biro Psikologi</span>
                    </div>
                </a>
                <div class="hidden md:flex items-center space-x-8 flex-1 justify-end">
                    
                    <div class="flex items-center gap-6 text-sm font-semibold">
                        <a href="<?php echo $path; ?>index.php" class="text-ralira-text hover:text-ralira-accent transition">Beranda</a>
                        <a href="<?php echo $path; ?>pages/public/services.php" class="text-ralira-text hover:text-ralira-accent transition">Layanan</a>
                        <a href="<?php echo $path; ?>pages/public/about.php" class="text-ralira-text hover:text-ralira-accent transition">Psikolog</a>
                        <a href="<?php echo $path; ?>pages/public/articles.php" class="text-ralira-text hover:text-ralira-accent transition">Artikel</a>
                    </div>

                    <a href="<?php echo $path; ?>pages/auth/login.php" class="bg-white text-ralira-text border border-white hover:border-ralira-primary hover:text-ralira-accent px-5 py-2.5 rounded-full font-bold transition flex items-center gap-2 text-sm shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" class="w-4 h-4" alt="Google">
                        Masuk / Daftar
                    </a>
                </div>

                <div class="md:hidden flex items-center gap-4">
                    <button class="text-ralira-text hover:text-ralira-accent transition focus:outline-none" onclick="toggleMobileMenu()">
                        <i class="fa-solid fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden bg-white/95 backdrop-blur-xl absolute w-full border-t border-white/50 shadow-xl">
            <div class="px-6 py-6 space-y-4">
                <a href="<?php echo $path; ?>index.php" class="block text-lg font-medium text-ralira-text hover:text-ralira-accent">Beranda</a>
                <a href="<?php echo $path; ?>pages/public/services.php" class="block text-lg font-medium text-ralira-text hover:text-ralira-accent">Layanan</a>
                <a href="<?php echo $path; ?>pages/public/about.php" class="block text-lg font-medium text-ralira-text hover:text-ralira-accent">Psikolog</a>
                <a href="<?php echo $path; ?>pages/public/articles.php" class="block text-lg font-medium text-ralira-text hover:text-ralira-accent">Artikel</a>
                <hr class="border-ralira-text/10">
                <a href="<?php echo $path; ?>pages/auth/login.php" class="w-full bg-ralira-primary text-white py-3 rounded-xl font-bold shadow-md flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-to-bracket"></i> Masuk Akun
                </a>
            </div>
        </div>
    </nav>