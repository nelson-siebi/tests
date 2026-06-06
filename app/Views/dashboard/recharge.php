<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Investian</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: { primary: '#3b82f6', success: '#10b981', warning: '#f59e0b', danger: '#ef4444' }
                }
            }
        }

        window.addEventListener('load', () => {
            const errorMsg = document.getElementById('error-msg');
            const successMsg = document.getElementById('success-msg');
            const msg = errorMsg || successMsg;
            if (msg) {
                msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    </script>
    <style>
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body class="bg-gray-50 text-slate-800 font-sans min-h-screen">
    <!-- Top Navbar (Same as Dashboard) -->
    <nav
        class="bg-white border-b border-gray-200 sticky top-0 z-50 py-3 px-6 md:px-12 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-3">
            <a href="/dashboard" class="hover:opacity-80 transition-opacity">
                <img src="/images/logo.png" alt="Investian" class="h-10 w-auto">
            </a>
        </div>

        <div class="hidden lg:flex items-center space-x-8">
            <a href="/dashboard"
                class="text-slate-600 hover:text-primary font-semibold transition-all"><?= __('dashboard') ?></a>
            <a href="/plans"
                class="text-slate-600 hover:text-primary font-semibold transition-all"><?= __('invest') ?></a>
            <a href="/ads" class="text-slate-600 hover:text-primary font-semibold transition-all"><?= __('ads') ?></a>
            <a href="/recharge" class="text-primary font-semibold transition-all"><?= __('wallet') ?></a>
            <a href="/withdraw"
                class="text-slate-600 hover:text-primary font-semibold transition-all"><?= __('withdrawal') ?></a>
        </div>

        <div class="flex items-center space-x-3">
            <!-- Community Icon -->
            <a href="/community"
                class="p-2 bg-gray-100 text-slate-600 rounded-lg hover:text-primary hover:bg-blue-50 transition-all relative">
                <i data-lucide="message-circle" class="w-5 h-5"></i>
                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
            </a>

            <?php if (\App\Core\Session::get('user_role') === 'admin'): ?>
                <a href="/admin"
                    class="p-2 bg-amber-50 text-amber-600 rounded-lg hover:text-amber-700 hover:bg-amber-100 transition-all flex items-center justify-center"
                    title="<?= __('admin_panel') ?>">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </a>
            <?php endif; ?>

            <div class="flex bg-gray-100 p-1 rounded-lg">
                <a href="/lang?l=en"
                    class="px-3 py-1 rounded-md text-xs font-bold uppercase <?= \App\Core\Language::getCurrent() === 'en' ? 'bg-white text-primary shadow-sm' : 'text-slate-400' ?>">EN</a>
                <a href="/lang?l=fr"
                    class="px-3 py-1 rounded-md text-xs font-bold uppercase <?= \App\Core\Language::getCurrent() === 'fr' ? 'bg-white text-primary shadow-sm' : 'text-slate-400' ?>">FR</a>
            </div>

            <!-- Profile Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false"
                    class="text-primary p-2 bg-blue-50 rounded-lg border border-blue-100 transition-all hover:bg-blue-100">
                    <i data-lucide="user" class="w-5 h-5"></i>
                </button>
                <div x-show="open" x-transition
                    class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
                    <a href="/profile"
                        class="flex items-center space-x-2 px-4 py-2 text-sm text-slate-600 hover:bg-gray-50 hover:text-primary transition-colors">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        <span class="font-bold"><?= __('profile') ?></span>
                    </a>
                    <?php if (\App\Core\Session::get('user_role') === 'admin'): ?>
                        <a href="/admin"
                            class="flex items-center space-x-2 px-4 py-2 text-sm text-slate-600 hover:bg-gray-50 hover:text-primary transition-colors">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            <span class="font-bold"><?= __('admin_panel') ?></span>
                        </a>
                    <?php endif; ?>
                    <hr class="my-1 border-gray-50">
                    <a href="/logout"
                        class="flex items-center space-x-2 px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span class="font-bold"><?= __('logout') ?></span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 md:p-10">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight mb-3"><?= __('recharge_title') ?> 💳</h1>
            <p class="text-slate-500 font-medium"><?= __('recharge_desc') ?></p>
            <a href="/guide"
                class="inline-flex items-center space-x-2 text-primary font-bold text-xs mt-4 hover:underline">
                <i data-lucide="help-circle" class="w-4 h-4"></i>
                <span><?= __('need_guide') ?></span>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Recharge Form -->
            <div class="lg:col-span-2">
                <div class="bg-white p-8 md:p-12 rounded-2xl shadow-sm border border-gray-100">
                    <?php if (isset($_GET['success'])): ?>
                        <div id="success-msg"
                            class="bg-emerald-50 border border-emerald-100 text-emerald-600 p-6 rounded-xl font-bold mb-8 flex items-center space-x-3 animate-fade-in">
                            <i data-lucide="check-circle" class="w-6 h-6"></i>
                            <span><?= __('payment_confirmed') ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="space-y-10">
                        <!-- Step 1: Amount -->
                        <div class="space-y-4">
                            <label
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?= __('step_1') ?>
                                : <?= __('amount_to_recharge') ?></label>

                            <input type="hidden" id="rechargeAmount" name="amount" required>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <?php
                                $amounts = [4000, 6000, 10000, 20000, 50000, 100000, 200000, 500000];
                                foreach ($amounts as $amt):
                                    ?>
                                    <button type="button" onclick="selectAmount(<?= $amt ?>, this)"
                                        class="amount-btn bg-white border-2 border-slate-200 text-slate-600 font-bold py-3 rounded-xl hover:border-primary hover:text-primary transition-all active:scale-95 text-sm">
                                        <?= number_format($amt, 0, '.', ' ') ?>
                                        <span class="text-[10px] uppercase">XAF</span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Step 2: Operator -->
                        <div class="space-y-4">
                            <label
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?= __('step_2') ?>
                                : <?= __('select_operator') ?></label>
                            <div class="grid grid-cols-2 gap-4">
                                <button onclick="selectOperator('orange')" id="btn-orange"
                                    class="operator-btn flex flex-col items-center p-4 border-2 border-gray-100 rounded-2xl hover:border-orange-200 transition-all hover:bg-orange-50/30">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/c/c8/Orange_logo.svg"
                                        class="h-10 w-10 mb-2 grayscale group-hover:grayscale-0" alt="Orange">
                                    <span class="text-xs font-bold text-slate-600"><?= __('orange_money') ?></span>
                                </button>
                                <button onclick="selectOperator('mtn')" id="btn-mtn"
                                    class="operator-btn flex flex-col items-center p-4 border-2 border-gray-100 rounded-2xl hover:border-yellow-200 transition-all hover:bg-yellow-50/30">
                                    <div
                                        class="h-10 w-10 mb-2 bg-yellow-400 rounded-lg flex items-center justify-center font-black text-xs">
                                        MTN</div>
                                    <span class="text-xs font-bold text-slate-600"><?= __('mtn_money') ?></span>
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: USSD Instructions -->
                        <div id="ussdSection" class="hidden animate-fade-in space-y-6">
                            <div class="bg-slate-900 text-white p-8 rounded-2xl shadow-xl space-y-6">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-sm font-bold uppercase tracking-widest text-slate-400">
                                        <?= __('step_by_step') ?>
                                    </h3>
                                    <span
                                        class="bg-primary/20 text-primary text-[10px] font-bold px-2 py-1 rounded"><?= __('payment_instructions') ?></span>
                                </div>

                                <div class="bg-white/5 border border-white/10 p-4 rounded-xl text-center">
                                    <p class="text-xs text-white/50 mb-2 font-medium"><?= __('copy_code') ?></p>
                                    <p id="ussdDisplay"
                                        class="text-xl md:text-2xl font-mono font-bold tracking-tight text-white mb-4">
                                    </p>
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <button onclick="copyUSSD()"
                                            class="flex-1 bg-white/10 hover:bg-white/20 py-3 rounded-lg text-xs font-bold transition-all flex items-center justify-center space-x-2">
                                            <i data-lucide="copy" class="w-4 h-4"></i>
                                            <span><?= __('copy') ?></span>
                                        </button>
                                        <a id="ussdCall" href="#"
                                            class="flex-1 bg-primary hover:bg-blue-600 py-3 rounded-lg text-xs font-bold transition-all flex items-center justify-center space-x-2">
                                            <i data-lucide="phone" class="w-4 h-4"></i>
                                            <span><?= __('launch_ussd') ?></span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Final Step: Reference -->
                            <form action="/recharge" method="POST" class="space-y-6 pt-4">
                                <input type="hidden" name="amount" id="formAmount">
                                <div class="space-y-3">
                                    <label
                                        class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?= __('transaction_reference') ?></label>
                                    <input type="text" name="reference" required
                                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-6 py-4 text-slate-900 font-bold text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                                        placeholder="Ex: TX-987654321">
                                </div>
                                <button type="submit"
                                    class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-5 rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-95 flex items-center justify-center space-x-3">
                                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                                    <span><?= __('validate_deposit') ?></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Sidebar -->
            <div class="space-y-6">
                <div class="flex items-center space-x-3 px-2">
                    <div class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center text-slate-400">
                        <i data-lucide="history" class="w-5 h-5"></i>
                    </div>
                    <h2 class="text-lg font-bold text-slate-900"><?= __('recent_history') ?></h2>
                </div>

                <div class="space-y-4">
                    <?php foreach ($transactions as $tx): ?>
                        <div
                            class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex justify-between items-center group hover:border-primary/30 transition-all">
                            <div>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">
                                    <?= date('d M, H:i', strtotime($tx['created_at'])) ?>
                                </p>
                                <h4 class="text-md font-bold text-slate-800"><?= number_format($tx['amount'], 0) ?>
                                    <?= __('cfa') ?>
                                </h4>
                            </div>
                            <?php if ($tx['status'] === 'completed'): ?>
                                <div class="bg-emerald-50 text-emerald-500 p-2 rounded-lg">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </div>
                            <?php else: ?>
                                <div class="bg-amber-50 text-amber-500 p-2 rounded-lg animate-pulse">
                                    <i data-lucide="clock" class="w-4 h-4"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($transactions)): ?>
                        <div class="text-center py-12 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            <p class="text-slate-400 text-sm font-medium"><?= __('no_deposits') ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Mobile Bottom Navigation -->
    <nav
        class="lg:hidden fixed bottom-4 left-4 right-4 bg-white/80 backdrop-blur-lg border border-gray-100 shadow-2xl rounded-2xl z-[100] px-4 py-3">
        <div class="flex justify-between items-center">
            <a href="/dashboard"
                class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary transition-colors">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]"><?= __('dashboard') ?></span>
            </a>
            <a href="/plans"
                class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary transition-colors">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]"><?= __('invest') ?></span>
            </a>
            <a href="/ads"
                class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary transition-colors">
                <i data-lucide="play-circle" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]"><?= __('ads') ?></span>
            </a>
            <a href="/recharge" class="flex flex-col items-center space-y-1 text-primary">
                <i data-lucide="wallet" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]"><?= __('wallet') ?></span>
            </a>
            <a href="/profile"
                class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary transition-colors">
                <i data-lucide="user" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]"><?= __('profile') ?></span>
            </a>
        </div>
    </nav>

    <script>
        let selectedOperator = null;
        const ussdConfigs = {
            orange: {
                base: '#150*1*1*656720564*',
                suffix: '#',
                call: 'tel:%23150*1*1*656720564*AMOUNT%23'
            },
            mtn: {
                base: '*126*9*682004136*',
                suffix: '#',
                call: 'tel:*126*9*682004136*AMOUNT%23'
            }
        };

        function selectAmount(amount, btn) {
            // Set hidden input
            document.getElementById('rechargeAmount').value = amount;

            // Reset all buttons
            document.querySelectorAll('.amount-btn').forEach(b => {
                b.classList.remove('border-primary', 'bg-primary/5', 'text-primary');
                b.classList.add('border-slate-200', 'bg-white', 'text-slate-600');
            });

            // Highlight selected
            btn.classList.remove('border-slate-200', 'bg-white', 'text-slate-600');
            btn.classList.add('border-primary', 'bg-primary/5', 'text-primary');
        }

        function selectOperator(op) {
            const amount = document.getElementById('rechargeAmount').value;
            if (!amount) {
                alert('<?= __('select_amount_alert') ?>');
                return;
            }

            selectedOperator = op;
            document.querySelectorAll('.operator-btn').forEach(b => b.classList.remove('border-primary', 'bg-primary/5', 'shadow-md'));
            document.getElementById(`btn-${op}`).classList.add('border-primary', 'bg-primary/5', 'shadow-md');

            updateUSSDFlow(op, amount);
        }

        function updateUSSDFlow(op, amount) {
            const config = ussdConfigs[op];
            const fullCode = config.base + amount + config.suffix;
            const callUrl = config.call.replace('AMOUNT', amount);

            document.getElementById('ussdDisplay').innerText = fullCode;
            document.getElementById('ussdCall').href = callUrl;
            document.getElementById('formAmount').value = amount;

            const section = document.getElementById('ussdSection');
            section.classList.remove('hidden');
            section.scrollIntoView({ behavior: 'smooth' });
        }

        function copyUSSD() {
            const code = document.getElementById('ussdDisplay').innerText;
            navigator.clipboard.writeText(code).then(() => {
                const btn = event.currentTarget.querySelector('span');
                const oldText = btn.innerText;
                btn.innerText = '<?= __('copied_success') ?>';
                setTimeout(() => btn.innerText = oldText, 2000);
            });
        }

        lucide.createIcons();
    </script>
    <?php include __DIR__ . '/../partials/whatsapp.php'; ?>
</body>

</html>