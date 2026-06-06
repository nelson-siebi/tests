<a href="https://wa.me/<?= env('SUPPORT_WHATSAPP', '237690000000') ?>" target="_blank"
    class="fixed bottom-24 right-6 md:bottom-10 md:right-10 bg-[#25D366] hover:bg-[#128C7E] text-white p-4 rounded-full shadow-2xl transition-all hover:scale-110 z-[90] flex items-center justify-center animate-bounce-slow"
    title="Contacter le support">
    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-circle">
        <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z" />
    </svg>
</a>
<style>
    .animate-bounce-slow {
        animation: bounce 3s infinite;
    }

    @keyframes bounce {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
    }
</style>