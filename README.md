# 🪐 Astrology — Hindu Astrological Platform

A full-stack astrology web application built with **Laravel (MVC)** and **Tailwind CSS**, performing all astrological calculations via pure mathematical logic — no external APIs used.

---

## ✨ Features

- **Birth Chart (Kundali)** — natal chart generation with house and planet placements
- **Vimshottari Dasha** — planetary period and sub-period calculations
- **ShadBala** — six-fold planetary strength computation
- **Shodashvarga** — 16 divisional chart calculations (D1 to D60)
- **Choghadiya & Muhurta** — auspicious time slot calculations
- **Hindu Festival Calendar** — festival and tithi rule-based matching
- **Tarabal & Murti Nirnay** — lunar constellation compatibility analysis
- **Gochar (Transit)** — current planetary transit tracking
- **Panchanga** — daily five-limb almanac (Tithi, Nakshatra, Yoga, Karana, Vara)

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8, Laravel (MVC), Eloquent ORM |
| Frontend | Blade Templates, Tailwind CSS |
| Database | MySQL |
| Tools | Composer, Git, VS Code |

---

## ⚙️ Setup & Installation

```bash
# 1. Clone the repository
git clone https://github.com/Priyanshi507/astrology.git
cd astrology

# 2. Install dependencies
composer install
npm install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Configure your database in .env
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

# 5. Run migrations and seed reference data
php artisan migrate --seed

# 6. Start the development server
php artisan serve
```

Visit `http://localhost:8000`

---

## 📁 Project Structure
app/

├── Http/

│   └── Controllers/

│       ├── LandingController.php

│       ├── KundaliController.php

│       ├── FestivalController.php

│       ├── MuhratController.php

│       ├── TarabalController.php

│       └── GocharController.php

│

├── Models/

│   ├── Planet.php

│   ├── NatalChart.php

│   ├── Nakshatra.php

│   ├── Tithi.php

│   ├── Yoga.php

│   ├── Karana.php

│   ├── Murti.php

│   ├── Tara.php

│   ├── ZodiacSign.php

│   ├── Weekday.php

│   ├── Ekadashi.php

│   ├── ChoghadiyaType.php

│   └── ChoghadiyaSequence.php

│

└── Services/

├── ChartRendering/

│   ├── AstroChartRenderer.php

│   ├── ShodashvargaCalculator.php

│   └── VargaChartRenderer.php

├── Dasha/

│   └── VimshottariDashaCalculator.php

├── Festival/

│   ├── HinduFestivalCalculator.php

│   └── TodayPanelService.php

├── Kundali/

│   └── KundaliService.php

├── Muhurta/

│   ├── MuhratCalculator.php

│   └── TarabalMurtiService.php

└── Planetary/

├── AstroCalculator.php

├── ShadBalaCalculator.php

├── GocharCalculator.php

└── TransitCalculator.php
database/

├── migrations/         # 15+ migration files

└── seeders/            # Reference data for all astrological tables
resources/

└── views/

├── layouts/

│   └── app.blade.php

└── partials/

├── chart/      # _main_chart, _house_signs, _detail_panel, _planet_summary...

├── dasha/      # _vimshottari, _details_tab

├── festival/   # _festivals, _muhurta_day, _panel_festival...

├── kundali/    # _panel_kundali

├── planetary/  # _gochar, _shadbala, _transit_calendar...

└── js/         # _scripts
---

## 🗄️ Database Schema

| Table | Description |
|-------|-------------|
| `planets` | 9 Vedic planets with attributes |
| `zodiac_signs` | 12 rashis with abbreviations |
| `nakshatras` | 27 lunar mansions |
| `tithis` | 30 lunar days |
| `yogas` | 27 yogas |
| `karanas` | 11 karanas |
| `weekdays` | 7 days with planetary rulers |
| `choghadiya_types` | Choghadiya categories |
| `choghadiya_sequences` | Day/night sequences |
| `taras` | 9 tara types |
| `murtis` | Murti classifications |
| `ekadashis` | Ekadashi names and rules |
| `natal_charts` | User birth chart data |
| `festival_rules` | Festival calculation rules |
| `astrological_houses` | 12 house definitions |

---

## 🔢 Calculation Approach

All astrological computations are performed using **pure mathematical algorithms** — no third-party astrology APIs are used. This includes:

- Planetary longitude calculations
- House cusp determination (Placidus / whole sign)
- Nakshatra and pada identification
- Tithi calculation from Sun-Moon angular difference
- Dasha balance from birth Nakshatra
- ShadBala weightage computation
- Varga chart divisional mapping

---

## 👩‍💻 Author

**Priyanshi Sharma**
- 🎓 B.Tech CSE, Lloyd Institute of Engineering & Technology (2024–2028)
- 🌟 Google Summer of Code 2026 — STE||AR Group (HPX)
- 💻 [GitHub](https://github.com/Priyanshi507)
- 🔗 [LinkedIn](https://linkedin.com/in/priyanshi-sharma-62a431337)

---

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).
