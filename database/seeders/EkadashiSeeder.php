<?php

namespace Database\Seeders;

use App\Models\Ekadashi;
use Illuminate\Database\Seeder;

class EkadashiSeeder extends Seeder
{
    public function run(): void
    {
        // Data from AstroCalculator::getEkadashiNames()
        // lookup_key matches the array key format used in PHP: 'Shukla_1', 'Krishna_1'…
        // vedic_month_number is the Purnimanta convention (1=Chaitra … 12=Phalguna)
        $masaNames = [
            1 => 'Chaitra', 2 => 'Vaishakha', 3 => 'Jyeshtha', 4 => 'Ashadha',
            5 => 'Shravana', 6 => 'Bhadrapada', 7 => 'Ashwin', 8 => 'Kartik',
            9 => 'Margashirsha', 10 => 'Pausha', 11 => 'Magha', 12 => 'Phalguna',
        ];

        // Columns: [key, name, paksha, monthNum, mantra, significance, rituals, auspTime, puranaRef]
        $rows = [
            ['Krishna_1',  'Papmochani Ekadashi',   'Krishna', 1,  'Om Namo Bhagavate Vasudevaya',
             'Remover of all sins — known and unknown.',
             ['Observe full fast', 'Vishnu Shodashopachar Puja', 'Listen to Katha'],
             'Sunrise to Dvadashi sunrise',
             'Chaitra Krishna 11. Brahma Vaivarta Purana: liberation from all sins.'],

            ['Shukla_1',   'Kamada Ekadashi',        'Shukla',  1,  'Om Namo Bhagavate Vasudevaya',
             'Fulfills all desires.',
             ['Observe full fast', 'Visit Vishnu temple', 'Chant Vishnu Sahasranama'],
             'Sunrise to Dvadashi sunrise',
             'Chaitra Shukla 11. Varaha Purana: all desires fulfilled.'],

            ['Krishna_2',  'Varuthini Ekadashi',     'Krishna', 2,  'Om Namo Bhagavate Vasudevaya',
             'Grants liberation from sins, equals 10,000 years of penance.',
             ['Observe Ekadashi fast', 'Worship Vamana Vishnu', 'Donate generously'],
             'Sunrise to Dvadashi sunrise',
             'Vaishakha Krishna 11. Protection from enemies, disease, and misfortune.'],

            ['Shukla_2',   'Mohini Ekadashi',        'Shukla',  2,  'Om Namo Bhagavate Vasudevaya',
             'Frees from illusion of the world.',
             ['Observe full fast', 'Worship Vishnu in Mohini form', 'Chant on Tulasi mala'],
             'Sunrise to Dvadashi',
             'Vaishakha Shukla 11. Liberation from the illusion of Maya.'],

            ['Krishna_3',  'Apara Ekadashi',         'Krishna', 3,  'Om Namo Bhagavate Vasudevaya',
             'Grants boundless merit, destroys gravest sins.',
             ['Observe Ekadashi fast', 'Read Vishnu Sahasranama', 'Feed the poor'],
             'Sunrise to Dvadashi sunrise',
             'Jyeshtha Krishna 11.'],

            ['Shukla_3',   'Nirjala Ekadashi',       'Shukla',  3,  'Om Namo Bhagavate Vasudevaya | Om Vishnave Namah',
             'Most austere — no water. Earns merit of all 24 Ekadashis combined.',
             ['Complete Nirjala fast — no food or water', 'Worship Vishnu with Tulasi', 'Donate water pots with fruits', 'Chant Vishnu Sahasranama 108 times'],
             'Entire Ekadashi day is continuous worship',
             'Jyeshtha Shukla 11 — the most austere. Not even water. King of all 24 Ekadashis.'],

            ['Krishna_4',  'Yogini Ekadashi',        'Krishna', 4,  'Om Namo Bhagavate Vasudevaya',
             'Heals physical and spiritual ailments.',
             ['Observe Ekadashi fast', 'Listen to Yogini story', 'Serve the sick'],
             'Sunrise to Dvadashi sunrise',
             'Ashadha Krishna 11. Removes physical and spiritual ailments.'],

            ['Shukla_4',   'Devshayani Ekadashi',    'Shukla',  4,  'Om Namo Bhagavate Vasudevaya',
             'Vishnu enters Yoga Nidra — Chaturmasya begins.',
             ['Observe Ekadashi fast with devotion', 'Worship sleeping Vishnu', 'Begin 4-month spiritual discipline', 'Light Akhanda Dipa'],
             'Evening and entire night — keep Akhanda Dipa burning',
             'Ashadha Shukla 11 — Vishnu begins Yoga Nidra. Chaturmas begins.'],

            ['Krishna_5',  'Kamika Ekadashi',        'Krishna', 5,  'Om Namo Bhagavate Vasudevaya | Tulasyai Namah',
             'Tulasi worship especially meritorious.',
             ['Observe Ekadashi fast', 'Special Tulasi puja', 'Chant on Tulasi mala', 'Read Bhagavata'],
             'Sunrise — begin with Tulasi puja',
             'Shravana Krishna 11. Tulasi Puja yields special merit.'],

            ['Shukla_5',   'Putrada Ekadashi',       'Shukla',  5,  'Om Namo Bhagavate Vasudevaya',
             'Grants virtuous offspring. Powerful in Shravana month.',
             ['Observe Ekadashi fast', 'Worship Vishnu for progeny', 'Chant Santana Gopala mantra'],
             'Sunrise to Dvadashi sunrise',
             'Shravana Shukla 11. Blesses couples with children.'],

            ['Krishna_6',  'Aja Ekadashi',           'Krishna', 6,  'Om Namo Bhagavate Vasudevaya',
             'Liberates from birth-death cycle. Frees ancestral souls.',
             ['Observe Ekadashi fast', 'Perform Pitru Tarpana', 'Worship Vishnu'],
             'Sunrise — Pitru Tarpana in afternoon',
             'Bhadrapada Krishna 11. Liberation from the cycle of birth and death.'],

            ['Shukla_6',   'Parsva Ekadashi',        'Shukla',  6,  'Om Namo Bhagavate Vasudevaya | Vamanaya Namah',
             'Vishnu turns sides in Yoga Nidra. Vamana Jayanti nearby.',
             ['Observe Ekadashi fast', 'Worship Vamana avatar', 'Offer yellow flowers to Vishnu', 'Recite Vamana Stotra'],
             'Sunrise — fast broken on Dvadashi',
             'Bhadrapada Shukla 11. Vishnu turns in Yoga Nidra.'],

            ['Krishna_7',  'Indira Ekadashi',        'Krishna', 7,  'Om Namo Bhagavate Vasudevaya',
             'Falls in Pitru Paksha. Most powerful for ancestral liberation.',
             ['Observe Ekadashi fast', 'Perform Pitru Tarpana and Shraddha', 'Worship Vishnu'],
             'Sunrise — Pitru Tarpana in afternoon',
             'Ashwin Krishna 11 — during Pitru Paksha. Best Ekadashi for removing Pitru Dosha.'],

            ['Shukla_7',   'Papankusha Ekadashi',    'Shukla',  7,  'Om Namo Bhagavate Vasudevaya | Om Padmanabhaya Namah',
             'Controls sins from multiple lifetimes. Opens gates of heaven.',
             ['Observe Ekadashi fast', 'Worship Padmanabha Vishnu', 'Keep night vigil', 'Recite Vishnu Sahasranama'],
             'Sunrise to Dvadashi sunrise',
             'Ashwin Shukla 11. Removes sins with an iron goad.'],

            ['Krishna_8',  'Rama Ekadashi',          'Krishna', 8,  'Om Shrim Mahalakshmyai Namah | Om Namo Bhagavate Vasudevaya',
             'Observed before Diwali to please Goddess Lakshmi.',
             ['Observe Ekadashi fast', 'Worship Lakshmi-Vishnu', 'Recite Mahalakshmi Stotra', 'Light lamps for Diwali preparation'],
             'Sunrise — pre-Diwali Lakshmi puja',
             'Kartik Krishna 11 — before Diwali.'],

            ['Shukla_8',   'Prabodhini Ekadashi',    'Shukla',  8,  'Om Namo Bhagavate Vasudevaya | Tulasyai Namah',
             'Vishnu awakens — Chaturmasya ends. All auspicious works resume.',
             ['Observe Ekadashi fast', 'Perform Tulasi Vivaha', 'Ring bells and blow conch', 'Light Akhanda Dipa'],
             'Evening at dusk — Tulasi Vivaha at Pradosha Kala',
             'Kartik Shukla 11 — Vishnu awakens. Chaturmas ends. Tulasi Vivaha.'],

            ['Krishna_9',  'Utpanna Ekadashi',       'Krishna', 9,  'Om Namo Bhagavate Vasudevaya | Om Vishnave Namah',
             'Birth of Ekadashi Devi. Observing this gives merit of all Ekadashis.',
             ['Observe Ekadashi fast', 'Worship Ekadashi Devi', 'Listen to Utpanna Katha', 'Keep night vigil'],
             'Sunrise to Dvadashi sunrise',
             'Margashirsha Krishna 11 — birth of Ekadashi Devi.'],

            ['Shukla_9',   'Mokshada Ekadashi',      'Shukla',  9,  'Om Namo Bhagavate Vasudevaya | Sarva-dharman Parityajya Mam Ekam Sharanam Vraja',
             'Grants liberation. Krishna delivered the Gita on this day — Gita Jayanti.',
             ['Read entire Bhagavad Gita', 'Observe Ekadashi fast', 'Donate copies of Bhagavad Gita'],
             'Sunrise — Gita recitation and Krishna puja',
             'Margashirsha Shukla 11 — Lord Krishna delivered the Gita (Gita Jayanti).'],

            ['Krishna_10', 'Saphala Ekadashi',       'Krishna', 10, 'Om Namo Narayanaya | Om Namo Bhagavate Vasudevaya',
             'Giver of success in all endeavors and spheres of life.',
             ['Observe Ekadashi fast', 'Worship Narayana', 'Recite Narayana Kavacha'],
             'Sunrise to Dvadashi sunrise',
             'Pausha Krishna 11. Success in all endeavours.'],

            ['Shukla_10',  'Putrada Ekadashi (Pausha)','Shukla',10, 'Om Namo Bhagavate Vasudevaya',
             'Pausha Putrada — grants and protects children.',
             ['Observe Ekadashi fast', 'Worship Vishnu for children', 'Chant Santana Gopala mantra'],
             'Sunrise to Dvadashi sunrise',
             'Pausha Shukla 11.'],

            ['Krishna_11', 'Shattila Ekadashi',      'Krishna', 11, 'Om Namo Bhagavate Vasudevaya',
             'Six-fold sesame ritual. Purifies karmic debt layer by layer.',
             ['Bathe with sesame water', 'Apply sesame paste', 'Perform sesame homa', 'Donate black sesame', 'Eat sesame sweets'],
             'Sunrise — sesame bath and donation are mandatory',
             'Magha Krishna 11. Six sesame (Tila) rituals.'],

            ['Shukla_11',  'Jaya Ekadashi',          'Shukla',  11, 'Om Namo Bhagavate Vasudevaya',
             'Grants victory and frees from ghostly afflictions.',
             ['Observe Ekadashi fast', 'Listen to Jaya Katha', 'Worship Vishnu', 'Keep night vigil'],
             'Sunrise to Dvadashi sunrise',
             'Magha Shukla 11. Victory over all enemies.'],

            ['Krishna_12', 'Vijaya Ekadashi',        'Krishna', 12, 'Om Ramaya Namah | Om Namo Bhagavate Vasudevaya',
             'Rama observed this before marching to Lanka. Grants victory in all battles.',
             ['Observe Ekadashi fast', 'Worship Rama-Vishnu', 'Read Ramayana', 'Chant Rama Nama'],
             'Sunrise — pray for victory',
             'Phalguna Krishna 11. Rama observed this vrat before departing for Lanka.'],

            ['Shukla_12',  'Amalaki Ekadashi',       'Shukla',  12, 'Om Namo Bhagavate Vasudevaya',
             'Amla tree worship — all deities reside in it. Donating Amla equals donating gold.',
             ['Observe Ekadashi fast', 'Worship the Amla tree', 'Eat food under the Amla tree', 'Donate Amla fruits'],
             'Sunrise — Amla tree worship before breaking fast',
             'Phalguna Shukla 11. Worship of the Amla (gooseberry) tree.'],
        ];

        foreach ($rows as [$key, $name, $paksha, $monthNum, $mantra, $significance, $rituals, $auspTime, $puranaRef]) {
            Ekadashi::updateOrCreate(
                ['lookup_key' => $key],
                [
                    'lookup_key'          => $key,
                    'name'                => $name,
                    'paksha'              => $paksha,
                    'vedic_month_number'  => $monthNum,
                    'vedic_month_name'    => $masaNames[$monthNum],
                    'mantra'              => $mantra,
                    'significance_text'   => $significance,
                    'rituals_list'        => $rituals,
                    'auspicious_time_note'=> $auspTime,
                    'purana_reference'    => $puranaRef,
                ]
            );
        }
    }
}
