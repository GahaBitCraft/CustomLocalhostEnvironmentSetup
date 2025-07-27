<?php
function getFileTimestamps($dir) {
    $mainFile = null;
    foreach (['index.php', 'index.html', 'main.php'] as $file) {
        if (file_exists("$dir/$file")) {
            $mainFile = "$dir/$file";
            break;
        }
    }
    return $mainFile ? [
        'created' => date("Y-m-d H:i", filectime($mainFile)),
        'modified' => date("Y-m-d H:i", filemtime($mainFile))
    ] : ['created' => 'N/A', 'modified' => 'N/A'];
}

$baseDir = __DIR__;
$projects = array_filter(scandir($baseDir), fn($item) =>
    is_dir("$baseDir/$item") && !in_array($item, ['.', '..', 'phpmyadmin', 'dashboard'])
);
sort($projects, SORT_NATURAL | SORT_FLAG_CASE);

$projectData = [];
foreach ($projects as $project) {
    $path = "$baseDir/$project";
    $configPath = "$path/config.json";
    $config = file_exists($configPath) ? json_decode(file_get_contents($configPath), true) : [];
    $timestamps = getFileTimestamps($path);
    $projectData[] = [
        'name' => $config['name'] ?? $project,
        'description' => $config['description'] ?? 'No description available.',
        'icon' => $config['icon'] ?? 'fa-folder',
        'thumbnail' => (isset($config['thumbnail']) && file_exists("$path/{$config['thumbnail']}")) ? "$project/{$config['thumbnail']}" : null,
        'folder' => $project,
        'created' => $timestamps['created'],
        'modified' => $timestamps['modified'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My XAMPP Projects</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    canvas#matrix-bg {
      position: fixed; top: 0; left: 0; z-index: -10;
      width: 100%; height: 100%;
      background: black; pointer-events: none;
    }
    .cursor-ripple {
      position: absolute;
      width: 20px; height: 20px;
      border-radius: 50%;
      background-color: rgba(0, 255, 0, 0.3);
      pointer-events: none;
      transform: translate(-50%, -50%);
      animation: rippleFade 0.6s ease-out forwards;
    }
    @keyframes rippleFade {
      to {
        opacity: 0;
        transform: translate(-50%, -50%) scale(5);
      }
    }
  </style>
</head>
<body class="bg-black text-white font-mono p-6 overflow-x-hidden">

<canvas id="matrix-bg"></canvas>

<div class="max-w-7xl mx-auto">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl sm:text-4xl font-bold text-green-400">🧠 My Localhost Projects</h1>
    <button id="toggleView" class="text-green-400 hover:text-green-200 text-2xl transition" title="Toggle View">
      <i id="viewIcon" class="fas fa-th-large"></i>
    </button>
  </div>

  <div id="projectContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($projectData as $p): ?>
    <!-- GRID VIEW CARD -->
    <div class="bg-gray-900 border border-green-500 rounded p-4 transition hover:scale-[1.02] view-grid">
      <?php if ($p['thumbnail']): ?>
        <img src="<?= $p['thumbnail'] ?>" alt="Thumb" class="w-full h-40 object-cover rounded">
      <?php else: ?>
        <div class="flex justify-center items-center h-40 text-green-400">
          <i class="fa-solid <?= $p['icon'] ?> text-5xl"></i>
        </div>
      <?php endif; ?>
      <div class="mt-4">
        <h2 class="text-xl text-green-300 font-semibold"><?= htmlspecialchars($p['name']) ?></h2>
        <p class="text-green-200 text-sm"><?= htmlspecialchars($p['description']) ?></p>
        <p class="text-xs mt-1 text-green-500">📅 Created: <?= $p['created'] ?> | 🕒 Updated: <?= $p['modified'] ?></p>
        <a href="/<?= $p['folder'] ?>/" class="inline-block mt-3 bg-green-600 hover:bg-green-700 px-4 py-2 text-white rounded">Open</a>
      </div>
    </div>

    <!-- LIST VIEW ROW -->
    <div class="hidden view-list flex items-center gap-4 border-b border-green-700 py-4">
      <?php if ($p['thumbnail']): ?>
        <img src="<?= $p['thumbnail'] ?>" alt="Thumb" class="w-20 h-20 object-cover rounded">
      <?php else: ?>
        <div class="w-20 h-20 flex justify-center items-center text-green-400 bg-gray-800 rounded">
          <i class="fa-solid <?= $p['icon'] ?> text-3xl"></i>
        </div>
      <?php endif; ?>
      <div class="flex-1">
        <h3 class="text-green-300 font-semibold text-lg"><?= htmlspecialchars($p['name']) ?></h3>
        <p class="text-green-400 text-sm"><?= htmlspecialchars($p['description']) ?></p>
        <p class="text-xs text-green-600 mt-1">📅 Created: <?= $p['created'] ?> | 🕒 Updated: <?= $p['modified'] ?></p>
      </div>
      <a href="/<?= $p['folder'] ?>/" class="text-green-300 hover:text-green-100 px-4 py-2 border border-green-500 rounded">Open</a>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Cursor Ripple Effect -->
<script>
document.addEventListener('mousemove', e => {
  const ripple = document.createElement('div');
  ripple.className = 'cursor-ripple';
  ripple.style.left = e.clientX + 'px';
  ripple.style.top = e.clientY + 'px';
  document.body.appendChild(ripple);
  setTimeout(() => ripple.remove(), 600);
});
</script>

<!-- Matrix Background -->
<script>
const canvas = document.getElementById("matrix-bg");
const ctx = canvas.getContext("2d");
canvas.height = window.innerHeight;
canvas.width = window.innerWidth;
const letters = "01";
const fontSize = 16;
const columns = canvas.width / fontSize;
const drops = Array(Math.floor(columns)).fill(1);

function drawMatrix() {
  ctx.fillStyle = "rgba(0, 0, 0, 0.05)";
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  ctx.fillStyle = "#0F0";
  ctx.font = fontSize + "px monospace";
  for (let i = 0; i < drops.length; i++) {
    const text = letters[Math.floor(Math.random() * letters.length)];
    ctx.fillText(text, i * fontSize, drops[i] * fontSize);
    if (drops[i] * fontSize > canvas.height || Math.random() > 0.975) drops[i] = 0;
    drops[i]++;
  }
}
setInterval(drawMatrix, 50);
window.addEventListener('resize', () => location.reload());
</script>

<!-- Toggle View Logic -->
<script>
document.getElementById('toggleView').addEventListener('click', () => {
  document.querySelectorAll('.view-grid').forEach(el => el.classList.toggle('hidden'));
  document.querySelectorAll('.view-list').forEach(el => el.classList.toggle('hidden'));
  const icon = document.getElementById('viewIcon');
  icon.classList.toggle('fa-th-large');
  icon.classList.toggle('fa-list');
});
</script>

</body>
</html>
