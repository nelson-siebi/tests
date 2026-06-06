<?php
require_once 'config/db.php';

try {
    $pdo = Database::getInstance()->getConnection();
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    die("Connection Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspecteur de Base de Données - Nelsius Pay</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'] },
                    colors: {
                        primary: '#6366f1',
                        secondary: '#8b5cf6',
                        dark: '#0f172a',
                        surface: '#1e293b'
                    }
                }
            }
        }
    </script>
    <style>
        /* Glassmorphism */
        .glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="bg-dark text-slate-300 antialiased min-h-screen flex flex-col">

    <!-- Header -->
    <header class="glass sticky top-0 z-50 border-b border-slate-700/50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                DB Inspector
            </h1>
            <div class="flex items-center gap-4">
               <span class="px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-sm font-medium border border-emerald-500/20">
                   Connected
               </span>
               <span class="text-sm text-slate-400"><?= count($tables) ?> Tables</span>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-7xl mx-auto w-full p-6 grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Sidebar Navigation -->
        <aside class="lg:col-span-3 lg:sticky lg:top-24 h-fit space-y-2 max-h-[calc(100vh-8rem)] overflow-y-auto pr-2 custom-scrollbar">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4 px-2">Tables</div>
            <?php foreach ($tables as $table): ?>
                <a href="#table-<?= $table ?>" 
                   class="block px-4 py-2 rounded-lg text-sm font-medium transition-all hover:bg-slate-800 hover:text-white hover:translate-x-1 border border-transparent hover:border-slate-700/50">
                    <?= $table ?>
                </a>
            <?php endforeach; ?>
        </aside>

        <!-- Main Content -->
        <div class="lg:col-span-9 space-y-12">
            
            <?php if (empty($tables)): ?>
                <div class="text-center py-20">
                    <div class="text-6xl mb-4">📭</div>
                    <h2 class="text-xl font-medium text-white">Aucune table trouvée</h2>
                    <p class="text-slate-500 mt-2">La base de données est vide.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($tables as $table): 
                // Fetch Structure
                $desc = $pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
                // Fetch Data
                $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
                $rowCount = count($rows);
            ?>
            <section id="table-<?= $table ?>" class="scroll-mt-28">
                <div class="glass rounded-xl overflow-hidden shadow-2xl shadow-black/20">
                    
                    <!-- Table Header -->
                    <div class="px-6 py-4 bg-slate-800/50 border-b border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                                <span class="w-2 h-6 rounded-full bg-gradient-to-b from-primary to-secondary"></span>
                                <?= $table ?>
                            </h2>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-700 text-slate-200 border border-slate-600">
                                <?= $rowCount ?> rows
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-700 text-slate-200 border border-slate-600">
                                <?= count($desc) ?> cols
                            </span>
                        </div>
                    </div>

                    <!-- Tabs/Content -->
                    <div class="p-6">
                        
                        <!-- Structure Peek (Collapsed by default logic in mind, simplified here for view) -->
                        <details class="group mb-6">
                            <summary class="flex items-center gap-2 cursor-pointer text-sm font-medium text-slate-400 hover:text-white transition-colors select-none">
                                <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                Voir la structure
                            </summary>
                            <div class="mt-4 overflow-x-auto rounded-lg border border-slate-700/50 bg-slate-900/30">
                                <table class="w-full text-left text-xs text-slate-400">
                                    <thead class="bg-slate-800/50 text-slate-200 uppercase font-semibold">
                                        <tr>
                                            <th class="px-4 py-2">Field</th>
                                            <th class="px-4 py-2">Type</th>
                                            <th class="px-4 py-2">Null</th>
                                            <th class="px-4 py-2">Key</th>
                                            <th class="px-4 py-2">Default</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-700/50">
                                        <?php foreach ($desc as $col): ?>
                                        <tr class="hover:bg-slate-800/30">
                                            <td class="px-4 py-2 font-mono text-primary font-medium"><?= $col['Field'] ?></td>
                                            <td class="px-4 py-2 text-yellow-500/80"><?= $col['Type'] ?></td>
                                            <td class="px-4 py-2"><?= $col['Null'] ?></td>
                                            <td class="px-4 py-2 text-emerald-400"><?= $col['Key'] ?></td>
                                            <td class="px-4 py-2"><?= $col['Default'] ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </details>

                        <!-- Data Table -->
                        <?php if ($rowCount > 0): ?>
                            <div class="overflow-x-auto rounded-lg border border-slate-700/50 bg-slate-900/50 max-h-[500px] custom-scrollbar">
                                <table class="w-full text-left text-sm whitespace-nowrap">
                                    <thead class="bg-slate-800 text-slate-200 sticky top-0 z-10 shadow-lg">
                                        <tr>
                                            <?php foreach (array_keys($rows[0]) as $header): ?>
                                                <th class="px-4 py-3 font-semibold border-b border-slate-600 first:pl-6 last:pr-6">
                                                    <?= $header ?>
                                                </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-700/50 text-slate-300">
                                        <?php foreach ($rows as $row): ?>
                                            <tr class="hover:bg-slate-800/30 transition-colors">
                                                <?php foreach ($row as $key => $val): ?>
                                                    <td class="px-4 py-3 border-r border-slate-700/30 last:border-r-0 first:pl-6 last:pr-6 max-w-xs truncate" title="<?= htmlspecialchars($val ?? 'NULL') ?>">
                                                        <?php if (is_null($val)): ?>
                                                            <span class="text-slate-600 italic">NULL</span>
                                                        <?php elseif (strlen($val) > 40): ?>
                                                            <?= htmlspecialchars(substr($val, 0, 40)) ?>...
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($val) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="py-8 text-center border border-dashed border-slate-700 rounded-lg bg-slate-800/20">
                                <p class="text-slate-500">Aucune donnée disponible</p>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </section>
            <?php endforeach; ?>

        </div>
    </main>
    
    <footer class="mt-12 py-6 border-t border-slate-800 text-center text-slate-600 text-sm">
        <p>Généré par Antigravity - <?= date('Y-m-d H:i:s') ?></p>
    </footer>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #0f172a;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
    </style>
</body>
</html>
