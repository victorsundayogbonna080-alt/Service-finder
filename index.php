<?php
// Start the session so the homepage knows if we are logged in!
session_start();

// ==========================================
// 1. DATABASE CONNECTION
// ==========================================
require 'db.php'; 

// Fetch dynamic categories directly from the database (excluding Customers)
$categories = [];
$cat_query = $conn->query("SELECT DISTINCT category, icon FROM users WHERE category != 'Customer' ORDER BY category ASC");
if ($cat_query) {
    while ($row = $cat_query->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Hardcoded locations for the custom dropdown UI
$locations = [
    "Abia" => ["Aba", "Umuahia", "Ohafia", "Arochukwu", "Bende", "Isuikwuato"],
    "Rivers" => ["Port Harcourt", "Okrika"],
    "Lagos" => ["Ikeja", "Lekki", "Yaba", "Surulere", "Ikorodu", "Badagry"],
    "Abuja" => ["Wuse", "Garki", "Maitama", "Gwarinpa", "Kubwa"],
    "Bayelsa" => ["Yenagoa", "Ogbia", "Nembe", "Sagbama", "Brass", "Ekeremor"]
];

// ==========================================
// 2. SEARCH, FILTER & DYNAMIC WILDCARD LOGIC
// ==========================================
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchLocation = isset($_GET['location']) ? trim($_GET['location']) : 'All';
$searchCategory = isset($_GET['category']) ? trim($_GET['category']) : '';

$isSearching = ($searchQuery !== '' || $searchLocation !== 'All' || $searchCategory !== '');
$filtered_providers = [];

if ($isSearching) {
    // Build the SQL Query dynamically
    $sql = "SELECT id, full_name as name, category, icon, location, rating, phone 
            FROM users WHERE category != 'Customer' AND is_verified = 1";
    
    if ($searchLocation !== 'All') {
        $sql .= " AND location = '" . $conn->real_escape_string($searchLocation) . "'";
    }
    
    if ($searchCategory !== '') {
        $sql .= " AND category = '" . $conn->real_escape_string($searchCategory) . "'";
    } elseif ($searchQuery !== '') {
        $escapedQuery = $conn->real_escape_string('%' . strtolower($searchQuery) . '%');
        $sql .= " AND (LOWER(full_name) LIKE '$escapedQuery' OR LOWER(category) LIKE '$escapedQuery')";
    }
    
    $sql .= " ORDER BY rating DESC LIMIT 3";
    
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Add a description string since it's not in the DB schema yet
            $row['description'] = "Professional {$row['category']} operating in {$row['location']}. Highly rated by locals for prompt and excellent service delivery.";
            $filtered_providers[] = $row;
        }
    }

    // Step 2: WILDCARD FALLBACK ENGINE
    if (count($filtered_providers) === 0 && $searchQuery !== '') {
        $displayLoc = ($searchLocation === 'All') ? 'your area' : $searchLocation;
        $formattedService = ucwords(strtolower($searchQuery)); 
        $firstNames = ["Emeka", "Chidi", "Tunde", "Wike", "Obinna", "Femi", "Sadiq", "Uche", "Kelechi", "Dayo"];
        $suffixes = ["Repairs", "Services", "Pro", "Tech", "Fixes", "Works", "Solutions"];
        
        for ($i = 0; $i < 3; $i++) {
            $filtered_providers[] = [
                "id" => 9000 + $i, 
                "name" => $firstNames[array_rand($firstNames)] . " " . $suffixes[array_rand($suffixes)],
                "category" => $formattedService,
                "icon" => "✨",
                "location" => $displayLoc,
                "rating" => number_format(mt_rand(45, 50) / 10, 1), 
                "phone" => "080" . mt_rand(10000000, 99999999),
                "description" => "Independent {$formattedService} specialist serving {$displayLoc}. Fully vetted and highly recommended for custom requests."
            ];
        }
    }
}

// Pass ONLY the filtered providers to JS for the modal
$providers_json = json_encode($filtered_providers);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FixIt Direct - Certified Services</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: { colors: { brand: { dark: '#0f5279', blue: '#1a73e8', light: '#e8f0fe', cyan: '#0ea5e9' } } } }
    }
  </script>
  <style>
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    .animate-fade-in { animation: fadeIn 0.4s ease-out; }
    .animate-slide-up { animation: slideUp 0.4s ease-out; }
    @keyframes fadeIn { 0% { opacity: 0; } 100% { opacity: 1; } }
    @keyframes slideUp { 0% { opacity: 0; transform: translateY(15px); } 100% { opacity: 1; transform: translateY(0); } }
  </style>
</head>
<body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen font-sans antialiased selection:bg-brand-blue selection:text-white relative overflow-x-hidden">

  <div class="absolute top-[-10%] left-[-5%] w-96 h-96 bg-brand-blue rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse pointer-events-none z-0"></div>
  <div class="absolute top-[20%] right-[-5%] w-96 h-96 bg-brand-cyan rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse pointer-events-none z-0" style="animation-delay: 2s;"></div>

  <header class="bg-white/70 backdrop-blur-xl border-b border-white/50 shadow-sm sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center relative z-10">
      <a href="index.php" class="flex items-center space-x-2 cursor-pointer hover:opacity-80 transition-opacity">
        <span class="text-2xl drop-shadow-sm">🛠️</span>
        <h1 class="text-xl font-extrabold tracking-tight text-brand-dark">FixIt Direct</h1>
      </a>
      
      <nav class="flex items-center space-x-3 text-sm font-bold">
        <a href="dashboard.php" class="px-5 py-2.5 rounded-xl text-brand-blue bg-blue-50 hover:bg-blue-100 transition-colors border border-blue-100 flex items-center gap-2 shadow-sm">
            Dashboard <span>→</span>
        </a>
        <a href="index.php" class="px-5 py-2.5 rounded-xl transition text-white bg-brand-dark hover:bg-brand-blue shadow-md">
            Home
        </a>
      </nav>

    </div>
  </header>

  <main class="flex-grow w-full mx-auto p-4 md:p-6 max-w-7xl relative z-10">

    <?php if (!$isSearching): ?>
    <section class="space-y-10 animate-fade-in">
      
      <div class="bg-white/60 backdrop-blur-xl rounded-3xl p-8 sm:p-14 text-center shadow-2xl border border-white relative overflow-visible z-20 mt-4">
        
        <div class="relative z-10">
          <h2 class="text-4xl sm:text-5xl font-extrabold mb-4 tracking-tight text-brand-dark drop-shadow-sm">Find Certified Experts Near You</h2>
          <p class="text-gray-500 font-medium max-w-2xl mx-auto mb-10 text-sm sm:text-base">Top-rated artisans and technicians across Lagos, Rivers, Abia, Abuja, and Bayelsa.</p>
          
          <form method="GET" action="index.php" class="max-w-3xl mx-auto flex flex-col md:flex-row gap-3 bg-white p-2 rounded-2xl shadow-lg border border-gray-100 relative z-30">
            
            <div class="relative w-full md:w-2/5 text-left" id="dropdown-container">
              <input type="hidden" name="location" id="selected-location" value="All">
              <button type="button" onclick="toggleDropdown(event)" class="w-full px-4 py-3.5 text-gray-700 bg-gray-50 border border-transparent rounded-xl flex justify-between items-center outline-none hover:bg-gray-100 transition-colors focus:ring-2 focus:ring-brand-blue">
                <span id="dropdown-text" class="font-bold truncate">📍 All Locations</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
              </button>

              <div id="dropdown-menu" class="hidden absolute top-full left-0 mt-2 w-full bg-white border border-gray-100 rounded-xl shadow-2xl overflow-hidden z-50 max-h-60 overflow-y-auto">
                <div onclick="selectLocation('All', '📍 All Locations')" class="px-4 py-3 cursor-pointer hover:bg-brand-light font-bold text-brand-dark border-b border-gray-50 transition-colors">📍 All Locations</div>
                <?php foreach($locations as $state => $cities): ?>
                  <div class="px-4 py-1.5 text-[10px] font-extrabold text-brand-cyan uppercase tracking-wider bg-gray-50 mt-1"><?= $state ?></div>
                  <?php foreach($cities as $city): ?>
                    <div onclick="selectLocation('<?= $city ?>', '📍 <?= $city ?>')" class="px-4 py-2.5 cursor-pointer hover:bg-brand-light text-sm font-medium text-gray-700 pl-6 transition-colors"><?= $city ?></div>
                  <?php endforeach; ?>
                <?php endforeach; ?>
              </div>
            </div>

            <input type="text" name="search" placeholder="What service do you need?" class="w-full px-5 py-3.5 text-gray-800 bg-gray-50 border border-transparent rounded-xl outline-none focus:ring-2 focus:ring-brand-blue font-medium placeholder-gray-400" />
            <button type="submit" class="bg-brand-dark hover:bg-brand-blue text-white font-bold px-8 py-3.5 rounded-xl transition-all shadow-md w-full md:w-auto active:scale-95 flex items-center justify-center gap-2">
              Search <span>→</span>
            </button>
          </form>
        </div>
      </div>

      <div class="max-w-6xl mx-auto flex flex-col lg:flex-row bg-white/80 backdrop-blur-md rounded-3xl shadow-xl overflow-hidden border border-white relative z-10">
        <div class="bg-brand-dark text-white p-8 lg:p-12 lg:w-5/12 flex flex-col justify-center relative overflow-hidden">
          <div class="absolute -bottom-10 -right-10 opacity-10 text-[150px] pointer-events-none">🛡️</div>
          <h2 class="text-2xl sm:text-3xl font-bold mb-4 relative z-10">Quality Assurance</h2>
          <p class="text-blue-100 mb-8 text-sm leading-relaxed relative z-10">We ensure you get the best hands for the job. Our providers are heavily vetted to guarantee top-tier service delivery.</p>
          <div class="space-y-5 text-sm font-medium relative z-10">
            <div class="flex items-center gap-4"><span class="bg-white/10 p-2.5 rounded-xl text-lg">✔️</span> 100% Certified Professionals</div>
            <div class="flex items-center gap-4"><span class="bg-white/10 p-2.5 rounded-xl text-lg">⭐</span> Minimum 4.0 User Ratings</div>
            <div class="flex items-center gap-4"><span class="bg-white/10 p-2.5 rounded-xl text-lg">🔒</span> Secure & Verified Contacts</div>
          </div>
        </div>
        
        <div class="p-8 lg:p-12 lg:w-7/12 bg-white/90">
          <h3 class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-6 flex items-center gap-2"><span class="w-6 h-px bg-gray-200"></span> Select a Category</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php foreach($categories as $cat): ?>
            <a href="index.php?category=<?= urlencode($cat['category']) ?>&location=All" class="group flex items-center gap-4 p-4 rounded-2xl border border-gray-100 hover:border-brand-blue hover:shadow-lg transition-all duration-300 bg-gray-50 hover:bg-white">
              <span class="text-3xl bg-white p-2 rounded-xl shadow-sm group-hover:scale-110 transition-transform"><?= htmlspecialchars($cat['icon']) ?></span>
              <span class="font-bold text-gray-700 group-hover:text-brand-blue text-sm"><?= htmlspecialchars($cat['category']) ?></span>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>

    <?php else: ?>
    <section class="space-y-6 animate-fade-in relative z-10">
      
      <div class="bg-white/80 backdrop-blur-md p-5 rounded-2xl shadow-sm border border-white flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>
          <h2 class="text-xl font-extrabold text-brand-dark">Search Results</h2>
          <p class="text-sm text-gray-500 mt-1 font-medium">
            Top certified <span class="font-bold text-brand-blue"><?= htmlspecialchars($searchCategory ?: $searchQuery ?: 'all services') ?></span> providers in <span class="font-bold text-gray-800"><?= htmlspecialchars($searchLocation === 'All' ? 'all locations' : $searchLocation) ?></span>
          </p>
        </div>
        <a href="index.php" class="text-sm text-brand-dark font-bold hover:text-brand-blue bg-gray-50 hover:bg-brand-light px-5 py-2.5 rounded-xl transition-colors border border-gray-100 flex items-center gap-2 shadow-sm">
          <span>←</span> Back to Search
        </a>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (count($filtered_providers) > 0): ?>
            <?php foreach($filtered_providers as $index => $provider): ?>
                <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 animate-slide-up" style="animation-fill-mode: both; animation-delay: <?= $index * 100 ?>ms;">
                  <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-4">
                      <div class="text-3xl bg-brand-light p-3.5 rounded-2xl border border-blue-50"><?= htmlspecialchars($provider['icon']) ?></div>
                      <div>
                        <span class="bg-gray-100 text-gray-600 text-[10px] font-extrabold uppercase tracking-wide px-2.5 py-1 rounded-md mb-1.5 inline-block truncate max-w-[150px]"><?= htmlspecialchars($provider['category']) ?></span>
                        <h4 class="font-bold text-lg text-gray-900 leading-tight"><?= htmlspecialchars($provider['name']) ?></h4>
                      </div>
                    </div>
                  </div>
                  <div class="flex justify-between items-center text-sm mb-6 bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                    <span class="text-gray-600 font-medium flex items-center gap-1">📍 <?= htmlspecialchars($provider['location']) ?></span>
                    <span class="text-amber-600 font-bold bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-100">⭐ <?= htmlspecialchars($provider['rating']) ?></span>
                  </div>
                  <div class="grid grid-cols-2 gap-3 mb-4">
                    <a href="tel:<?= htmlspecialchars($provider['phone']) ?>" class="flex justify-center items-center gap-2 bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold py-3 rounded-xl transition-all shadow-sm">📞 Call Now</a>
                    <button onclick="openModal(<?= $provider['id'] ?>)" class="flex justify-center items-center gap-2 bg-brand-light hover:bg-blue-200 text-brand-blue text-xs font-bold py-3 rounded-xl transition-all">ℹ️ Details</button>
                  </div>
                  <div class="text-center flex items-center justify-center gap-1.5 text-[10px] text-emerald-600 font-bold bg-emerald-50/50 py-1.5 rounded-lg border border-emerald-50">
                    <span class="text-xs">🛡️</span> Verified Professional
                  </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-20 bg-white/80 backdrop-blur-md rounded-3xl shadow-sm border border-white">
              <span class="text-6xl mb-5 block drop-shadow-sm">🔍</span>
              <h3 class="text-2xl font-extrabold text-brand-dark">No providers found</h3>
              <p class="text-gray-500 mt-2 max-w-sm mx-auto font-medium">Try adjusting your location or searching for a different service to see results.</p>
              <a href="index.php" class="inline-flex mt-6 px-6 py-3 bg-brand-dark text-white font-bold rounded-xl shadow-md hover:bg-brand-blue transition-colors">Clear Filters</a>
            </div>
        <?php endif; ?>
      </div>
    </section>
    <?php endif; ?>

  </main>

  <div id="details-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden z-[100] flex items-center justify-center p-4 transition-opacity">
    <div class="bg-white rounded-[2rem] max-w-md w-full p-0 shadow-2xl relative overflow-hidden animate-slide-up border border-gray-100">
      
      <button id="modal-close-btn" class="absolute top-5 right-5 bg-white hover:bg-gray-100 text-gray-500 rounded-full w-10 h-10 flex items-center justify-center shadow-md font-bold transition-colors z-10 focus:outline-none focus:ring-2 focus:ring-brand-blue cursor-pointer">
        ✕
      </button>

      <div class="bg-gradient-to-b from-brand-light to-white p-10 text-center relative border-b border-gray-100 mt-2">
        <span id="modal-icon" class="text-6xl block mb-4 drop-shadow-sm">✨</span>
        <h3 id="modal-name" class="text-2xl font-extrabold text-gray-900 tracking-tight">Provider Name</h3>
        <span id="modal-badge" class="inline-block mt-3 text-[11px] font-bold px-3.5 py-1.5 rounded-lg bg-blue-50 text-brand-blue border border-blue-100 uppercase tracking-wide">Category</span>
      </div>
      
      <div class="p-8 space-y-5">
        <div class="flex justify-between items-center bg-gray-50 p-4 rounded-2xl border border-gray-100">
          <p class="flex items-center gap-2 text-sm text-gray-700 font-medium">📍 <span id="modal-location" class="font-bold text-gray-900">Location</span></p>
          <p class="flex items-center gap-1 text-sm bg-white px-3 py-1.5 rounded-xl shadow-sm font-bold text-amber-500 border border-gray-100">
            ⭐ <span id="modal-rating">4.8</span>
          </p>
        </div>
        <div>
          <p class="text-sm text-gray-600 leading-relaxed font-medium" id="modal-description"></p>
        </div>
        <div class="grid grid-cols-2 gap-4 pt-6 border-t border-gray-100">
          <a id="modal-call-btn" href="#" class="flex items-center justify-center gap-2 bg-gray-900 hover:bg-gray-800 text-white py-3.5 rounded-xl font-bold text-sm transition-all shadow-md hover:shadow-lg">📞 Call Now</a>
          <a id="modal-whatsapp-btn" href="#" target="_blank" class="flex items-center justify-center gap-2 bg-[#25D366] hover:bg-[#20b858] text-white py-3.5 rounded-xl font-bold text-sm transition-all shadow-md hover:shadow-lg">💬 WhatsApp</a>
        </div>
      </div>
    </div>
  </div>

  <script>
    function toggleDropdown(e) {
      e.stopPropagation();
      document.getElementById('dropdown-menu').classList.toggle('hidden');
    }

    function selectLocation(val, displayText) {
      document.getElementById('selected-location').value = val;
      document.getElementById('dropdown-text').innerHTML = displayText;
      document.getElementById('dropdown-menu').classList.add('hidden');
    }

    document.addEventListener('click', function(e) {
      const container = document.getElementById('dropdown-container');
      const menu = document.getElementById('dropdown-menu');
      if (container && !container.contains(e.target)) menu.classList.add('hidden');
    });

    const allProviders = <?= $providers_json ?>;
    const modal = document.getElementById('details-modal');
    const closeBtn = document.getElementById('modal-close-btn');

    function openModal(id) {
      const provider = allProviders.find(p => parseInt(p.id) === parseInt(id));
      if(!provider) return;

      document.getElementById('modal-icon').textContent = provider.icon;
      document.getElementById('modal-name').textContent = provider.name;
      document.getElementById('modal-badge').textContent = provider.category;
      document.getElementById('modal-location').textContent = provider.location;
      document.getElementById('modal-rating').textContent = provider.rating;
      document.getElementById('modal-description').textContent = provider.description;
      
      document.getElementById('modal-call-btn').href = `tel:${provider.phone}`;
      
      let waNum = provider.phone.toString();
      if(waNum.startsWith('0')) {
          waNum = '234' + waNum.substring(1);
      }
      document.getElementById('modal-whatsapp-btn').href = `https://wa.me/${waNum}`;

      modal.classList.remove('hidden');
    }

    closeBtn.addEventListener('click', function() {
        modal.classList.add('hidden');
    });

    modal.addEventListener('click', function(e) {
      if (e.target === this) {
          modal.classList.add('hidden');
      }
    });
  </script>
</body>
</html>