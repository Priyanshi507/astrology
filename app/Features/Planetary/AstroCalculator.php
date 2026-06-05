<?php

namespace App\Features\Planetary;

/**
 * AstroCalculator — PHP port of astro.js
 *
 * Engine : Jean Meeus "Astronomical Algorithms" (2nd Ed.)
 * Planets: Sun, Moon, Mercury, Venus, Mars, Jupiter, Saturn,
 *          Rahu (North Node), Ketu (South Node)
 * Added  : Ascendant (Lagna), Descendant, MC, IC
 *          Tithi, Karana, Vara, Nakshatra, Yoga (Panchanga)
 *          Vimshottari Dasha balance
 *          Sunrise / Sunset
 *          Monthly Masa Panchanga with Samapti Kaal
 *          Chandra Rashi Pravesh (Moon sign changes)
 */
class AstroCalculator
{
    // ── Constants ──────────────────────────────────────────────────
    private const DEG = M_PI / 180.0;

    // ── Static data tables ─────────────────────────────────────────

    private static array $TITHIS = [
        ['n'=>'Pratipada',   'paksha'=>'Shukla',  'num'=>1,  'lord'=>'Agni',      'nature'=>'Nanda (Auspicious)',   'deity'=>'Brahma'    ],
        ['n'=>'Dwitiya',     'paksha'=>'Shukla',  'num'=>2,  'lord'=>'Brahma',    'nature'=>'Bhadra (Prosperous)',  'deity'=>'Vidhatr'   ],
        ['n'=>'Tritiya',     'paksha'=>'Shukla',  'num'=>3,  'lord'=>'Kartikeya', 'nature'=>'Jaya (Victorious)',    'deity'=>'Gauri'     ],
        ['n'=>'Chaturthi',   'paksha'=>'Shukla',  'num'=>4,  'lord'=>'Yama',      'nature'=>'Rikta (Inauspicious)', 'deity'=>'Ganesh'    ],
        ['n'=>'Panchami',    'paksha'=>'Shukla',  'num'=>5,  'lord'=>'Moon',      'nature'=>'Purna (Full)',         'deity'=>'Naga'      ],
        ['n'=>'Shashthi',    'paksha'=>'Shukla',  'num'=>6,  'lord'=>'Kartikeya', 'nature'=>'Nanda',                'deity'=>'Kartikeya' ],
        ['n'=>'Saptami',     'paksha'=>'Shukla',  'num'=>7,  'lord'=>'Sun',       'nature'=>'Bhadra',               'deity'=>'Surya'     ],
        ['n'=>'Ashtami',     'paksha'=>'Shukla',  'num'=>8,  'lord'=>'Shiva',     'nature'=>'Rikta',                'deity'=>'Rudra'     ],
        ['n'=>'Navami',      'paksha'=>'Shukla',  'num'=>9,  'lord'=>'Durga',     'nature'=>'Jaya',                 'deity'=>'Durga'     ],
        ['n'=>'Dashami',     'paksha'=>'Shukla',  'num'=>10, 'lord'=>'Yama',      'nature'=>'Purna',                'deity'=>'Dharma'    ],
        ['n'=>'Ekadashi',    'paksha'=>'Shukla',  'num'=>11, 'lord'=>'Vishnu',    'nature'=>'Jaya',                 'deity'=>'Vishnu'    ],
        ['n'=>'Dwadashi',    'paksha'=>'Shukla',  'num'=>12, 'lord'=>'Vishnu',    'nature'=>'Nanda',                'deity'=>'Hari'      ],
        ['n'=>'Trayodashi',  'paksha'=>'Shukla',  'num'=>13, 'lord'=>'Kama',      'nature'=>'Jaya',                 'deity'=>'Kama'      ],
        ['n'=>'Chaturdashi', 'paksha'=>'Shukla',  'num'=>14, 'lord'=>'Shiva',     'nature'=>'Rikta',                'deity'=>'Shiva'     ],
        ['n'=>'Purnima',     'paksha'=>'Shukla',  'num'=>15, 'lord'=>'Moon',      'nature'=>'Purna',                'deity'=>'Moon'      ],
        ['n'=>'Pratipada',   'paksha'=>'Krishna', 'num'=>1,  'lord'=>'Agni',      'nature'=>'Nanda',                'deity'=>'Brahma'    ],
        ['n'=>'Dwitiya',     'paksha'=>'Krishna', 'num'=>2,  'lord'=>'Brahma',    'nature'=>'Bhadra',               'deity'=>'Vidhatr'   ],
        ['n'=>'Tritiya',     'paksha'=>'Krishna', 'num'=>3,  'lord'=>'Kartikeya', 'nature'=>'Jaya',                 'deity'=>'Gauri'     ],
        ['n'=>'Chaturthi',   'paksha'=>'Krishna', 'num'=>4,  'lord'=>'Yama',      'nature'=>'Rikta',                'deity'=>'Ganesh'    ],
        ['n'=>'Panchami',    'paksha'=>'Krishna', 'num'=>5,  'lord'=>'Moon',      'nature'=>'Purna',                'deity'=>'Naga'      ],
        ['n'=>'Shashthi',    'paksha'=>'Krishna', 'num'=>6,  'lord'=>'Kartikeya', 'nature'=>'Nanda',                'deity'=>'Kartikeya' ],
        ['n'=>'Saptami',     'paksha'=>'Krishna', 'num'=>7,  'lord'=>'Sun',       'nature'=>'Bhadra',               'deity'=>'Surya'     ],
        ['n'=>'Ashtami',     'paksha'=>'Krishna', 'num'=>8,  'lord'=>'Shiva',     'nature'=>'Rikta',                'deity'=>'Rudra'     ],
        ['n'=>'Navami',      'paksha'=>'Krishna', 'num'=>9,  'lord'=>'Durga',     'nature'=>'Jaya',                 'deity'=>'Durga'     ],
        ['n'=>'Dashami',     'paksha'=>'Krishna', 'num'=>10, 'lord'=>'Yama',      'nature'=>'Purna',                'deity'=>'Dharma'    ],
        ['n'=>'Ekadashi',    'paksha'=>'Krishna', 'num'=>11, 'lord'=>'Vishnu',    'nature'=>'Jaya',                 'deity'=>'Vishnu'    ],
        ['n'=>'Dwadashi',    'paksha'=>'Krishna', 'num'=>12, 'lord'=>'Vishnu',    'nature'=>'Nanda',                'deity'=>'Hari'      ],
        ['n'=>'Trayodashi',  'paksha'=>'Krishna', 'num'=>13, 'lord'=>'Kama',      'nature'=>'Jaya',                 'deity'=>'Kama'      ],
        ['n'=>'Chaturdashi', 'paksha'=>'Krishna', 'num'=>14, 'lord'=>'Shiva',     'nature'=>'Rikta',                'deity'=>'Shiva'     ],
        ['n'=>'Amavasya',    'paksha'=>'Krishna', 'num'=>15, 'lord'=>'Pitrs',     'nature'=>'Nanda',                'deity'=>'Pitrs'     ],
    ];

    private static array $KARANA_CYCLE = [
        ['n'=>'Bava',    'lord'=>'Indra',   'nature'=>'Movable',      'type'=>'Chara',  'deity'=>'Indra',   'favour'=>'Auspicious acts, travel',      'cls'=>'Movable (Chara)'],
        ['n'=>'Balava',  'lord'=>'Brahma',  'nature'=>'Movable',      'type'=>'Chara',  'deity'=>'Brahma',  'favour'=>'Creative work, rituals',        'cls'=>'Movable (Chara)'],
        ['n'=>'Kaulava', 'lord'=>'Mitra',   'nature'=>'Movable',      'type'=>'Chara',  'deity'=>'Mitra',   'favour'=>'Friendship, partnerships',      'cls'=>'Movable (Chara)'],
        ['n'=>'Taitila', 'lord'=>'Aryama',  'nature'=>'Movable',      'type'=>'Chara',  'deity'=>'Aryaman', 'favour'=>'Domestic activities',           'cls'=>'Movable (Chara)'],
        ['n'=>'Garija',  'lord'=>'Prithvi', 'nature'=>'Movable',      'type'=>'Chara',  'deity'=>'Bhumi',   'favour'=>'Agriculture, earth work',       'cls'=>'Movable (Chara)'],
        ['n'=>'Vanija',  'lord'=>'Lakshmi', 'nature'=>'Movable',      'type'=>'Chara',  'deity'=>'Lakshmi', 'favour'=>'Trade, commerce, prosperity',   'cls'=>'Movable (Chara)'],
        ['n'=>'Vishti',  'lord'=>'Yama',    'nature'=>'Inauspicious', 'type'=>'Chara',  'deity'=>'Yama',    'favour'=>'Avoid new beginnings',          'cls'=>'Movable (Chara)'],
    ];

    private static array $KARANA_FIXED = [
        ['n'=>'Kimstughna',  'lord'=>'Sun',    'nature'=>'Auspicious',   'type'=>'Sthira','deity'=>'Surya',  'favour'=>'Auspicious acts',    'cls'=>'Fixed (Sthira)'],
        ['n'=>'Shakuni',     'lord'=>'Vishnu', 'nature'=>'Mixed',        'type'=>'Sthira','deity'=>'Vishnu', 'favour'=>'Mixed results',      'cls'=>'Fixed (Sthira)'],
        ['n'=>'Chatushpada', 'lord'=>'Brahma', 'nature'=>'Auspicious',   'type'=>'Sthira','deity'=>'Rudra',  'favour'=>'Stability, rituals', 'cls'=>'Fixed (Sthira)'],
        ['n'=>'Naga',        'lord'=>'Vasuki', 'nature'=>'Inauspicious', 'type'=>'Sthira','deity'=>'Naga',   'favour'=>'Avoid new acts',     'cls'=>'Fixed (Sthira)'],
    ];

    private static array $VARAS = [
        ['n'=>'Ravivara',    'en'=>'Sunday',    'lord'=>'Sun',     'sym'=>'☀','color'=>'#d4760a','nature'=>'Ugra (Fierce)',   'deity'=>'Surya',      'deityNote'=>'Lord of light and soul',          'horaLord'=>'Sun',     'classification'=>'Ugra',   'classNote'=>'Fierce — suited for bold acts',      'auspicious'=>'Travel, authority, medicine',           'info'=>'Sunday is ruled by the Sun (Surya). Excellent for activities relating to government, authority, father, medicine, and gold. The Sun-hora at sunrise amplifies power and confidence. Avoid confrontational disputes.'],
        ['n'=>'Somavara',    'en'=>'Monday',    'lord'=>'Moon',    'sym'=>'☽','color'=>'#1d4e6f','nature'=>'Saumya (Gentle)', 'deity'=>'Chandra',    'deityNote'=>'Lord of mind and emotions',       'horaLord'=>'Moon',    'classification'=>'Saumya', 'classNote'=>'Gentle — suited for nurturing acts', 'auspicious'=>'Family, travel, agriculture, healing', 'info'=>'Monday is ruled by the Moon (Chandra / Soma). Ideal for activities related to mother, home, water, emotions, and agriculture. Favourable for starting journeys northward.'],
        ['n'=>'Mangalavara', 'en'=>'Tuesday',   'lord'=>'Mars',    'sym'=>'♂','color'=>'#b83020','nature'=>'Ugra (Fierce)',   'deity'=>'Mangala',    'deityNote'=>'Lord of energy and courage',      'horaLord'=>'Mars',    'classification'=>'Ugra',   'classNote'=>'Fierce — suited for courageous acts','auspicious'=>'Physical work, surgery, law enforcement','info'=>'Tuesday is ruled by Mars (Mangala). Strong for activities requiring courage, physical exertion, surgery, and military matters.'],
        ['n'=>'Budhavara',   'en'=>'Wednesday', 'lord'=>'Mercury', 'sym'=>'☿','color'=>'#2e7a6e','nature'=>'Saumya (Gentle)', 'deity'=>'Budha',      'deityNote'=>'Lord of intellect and communication','horaLord'=>'Mercury','classification'=>'Saumya', 'classNote'=>'Gentle — suited for intellectual acts','auspicious'=>'Business, communication, education, trade','info'=>'Wednesday is ruled by Mercury (Budha). Excellent for trade, communication, writing, education, and business contracts.'],
        ['n'=>'Guruvara',    'en'=>'Thursday',  'lord'=>'Jupiter', 'sym'=>'♃','color'=>'#7a5a10','nature'=>'Guru (Auspicious)','deity'=>'Brihaspati', 'deityNote'=>'Lord of wisdom and dharma',       'horaLord'=>'Jupiter', 'classification'=>'Guru',   'classNote'=>'Auspicious — best for sacred acts',  'auspicious'=>'Rituals, education, guru worship, marriage','info'=>'Thursday is ruled by Jupiter (Guru/Brihaspati). The most auspicious day for beginning spiritual practices, religious ceremonies, education, and marriage.'],
        ['n'=>'Shukravara',  'en'=>'Friday',    'lord'=>'Venus',   'sym'=>'♀','color'=>'#8e3a7a','nature'=>'Saumya (Gentle)', 'deity'=>'Shukra',     'deityNote'=>'Lord of love, arts, and luxury',  'horaLord'=>'Venus',   'classification'=>'Saumya', 'classNote'=>'Gentle — suited for arts and love',  'auspicious'=>'Marriage, arts, beauty, romance, luxury','info'=>'Friday is ruled by Venus (Shukra). Ideal for love, art, music, beauty treatments, and sensory pleasures.'],
        ['n'=>'Shanivara',   'en'=>'Saturday',  'lord'=>'Saturn',  'sym'=>'♄','color'=>'#4a4060','nature'=>'Sthira (Stable)', 'deity'=>'Shani',      'deityNote'=>'Lord of karma and discipline',    'horaLord'=>'Saturn',  'classification'=>'Sthira', 'classNote'=>'Stable — suited for enduring acts',  'auspicious'=>'Long-term planning, discipline, oil treatments','info'=>'Saturday is ruled by Saturn (Shani). Best for activities requiring persistence, discipline, and long-term commitment.'],
    ];

    private static array $YOGAS = [
        ['n'=>'Vishkambha', 'nature'=>'Inauspicious','lord'=>'Saturn',  'deity'=>'Yama',       'cls'=>'Mahavisha','desc'=>'Obstructed progress; avoid starting important work'     ],
        ['n'=>'Priti',      'nature'=>'Auspicious',  'lord'=>'Mercury', 'deity'=>'Vishnu',     'cls'=>'Subha',   'desc'=>'Love and affection flourish; good for relationships'     ],
        ['n'=>'Ayushman',   'nature'=>'Auspicious',  'lord'=>'Saturn',  'deity'=>'Brahma',     'cls'=>'Subha',   'desc'=>'Long life and health; good for medical treatments'       ],
        ['n'=>'Saubhagya',  'nature'=>'Auspicious',  'lord'=>'Jupiter', 'deity'=>'Lakshmi',    'cls'=>'Subha',   'desc'=>'Fortune and prosperity; excellent for all undertakings'  ],
        ['n'=>'Shobhana',   'nature'=>'Auspicious',  'lord'=>'Mars',    'deity'=>'Brihaspati', 'cls'=>'Subha',   'desc'=>'Brilliance and beauty; good for arts and beautification'  ],
        ['n'=>'Atiganda',   'nature'=>'Inauspicious','lord'=>'Sun',     'deity'=>'Moon',       'cls'=>'Ashubha', 'desc'=>'Accidents and obstacles; proceed with caution'           ],
        ['n'=>'Sukarma',    'nature'=>'Auspicious',  'lord'=>'Jupiter', 'deity'=>'Indra',      'cls'=>'Subha',   'desc'=>'Good deeds rewarded; excellent for charitable acts'       ],
        ['n'=>'Dhriti',     'nature'=>'Auspicious',  'lord'=>'Saturn',  'deity'=>'Apsaras',    'cls'=>'Subha',   'desc'=>'Steadfastness and resolve; good for commitments'         ],
        ['n'=>'Shoola',     'nature'=>'Inauspicious','lord'=>'Mars',    'deity'=>'Rudra',      'cls'=>'Ashubha', 'desc'=>'Sharp pain and conflict; avoid confrontations'           ],
        ['n'=>'Ganda',      'nature'=>'Inauspicious','lord'=>'Sun',     'deity'=>'Agni',       'cls'=>'Ashubha', 'desc'=>'Danger and strife; be cautious with fire and sharp tools'],
        ['n'=>'Vriddhi',    'nature'=>'Auspicious',  'lord'=>'Moon',    'deity'=>'Jaya',       'cls'=>'Subha',   'desc'=>'Growth and increase; excellent for investments and gains' ],
        ['n'=>'Dhruva',     'nature'=>'Auspicious',  'lord'=>'Mars',    'deity'=>'Brahma',     'cls'=>'Subha',   'desc'=>'Permanence and stability; good for laying foundations'    ],
        ['n'=>'Vyaghata',   'nature'=>'Inauspicious','lord'=>'Sun',     'deity'=>'Vayu',       'cls'=>'Ashubha', 'desc'=>'Sudden losses; avoid new ventures and travel'            ],
        ['n'=>'Harshana',   'nature'=>'Auspicious',  'lord'=>'Mercury', 'deity'=>'Bhaga',      'cls'=>'Subha',   'desc'=>'Joy and delight; good for celebrations and entertainment'],
        ['n'=>'Vajra',      'nature'=>'Inauspicious','lord'=>'Jupiter', 'deity'=>'Varuna',     'cls'=>'Ashubha', 'desc'=>'Thunderbolt — harsh results; be careful with water'      ],
        ['n'=>'Siddhi',     'nature'=>'Auspicious',  'lord'=>'Venus',   'deity'=>'Ganesha',    'cls'=>'Subha',   'desc'=>'Accomplishment; best yoga for beginning any important act'],
        ['n'=>'Vyatipata',  'nature'=>'Inauspicious','lord'=>'Rahu',    'deity'=>'Rudra',      'cls'=>'Mahavisha','desc'=>'Calamity; a very inauspicious yoga — avoid all new starts'],
        ['n'=>'Variyana',   'nature'=>'Auspicious',  'lord'=>'Venus',   'deity'=>'Kubera',     'cls'=>'Subha',   'desc'=>'Wealth and comfort; good for luxury and financial matters'],
        ['n'=>'Parigha',    'nature'=>'Inauspicious','lord'=>'Sun',     'deity'=>'Vishwakarma','cls'=>'Ashubha', 'desc'=>'Barrier and obstruction; difficult to complete tasks'    ],
        ['n'=>'Shiva',      'nature'=>'Auspicious',  'lord'=>'Mercury', 'deity'=>'Shiva',      'cls'=>'Subha',   'desc'=>'Divine grace; excellent for spiritual worship and puja'  ],
        ['n'=>'Siddha',     'nature'=>'Auspicious',  'lord'=>'Jupiter', 'deity'=>'Ganesha',    'cls'=>'Subha',   'desc'=>'Perfect accomplishment; all works succeed with ease'     ],
        ['n'=>'Sadhya',     'nature'=>'Auspicious',  'lord'=>'Venus',   'deity'=>'Chandra',    'cls'=>'Subha',   'desc'=>'Achievable goals; moderate effort yields good results'    ],
        ['n'=>'Shubha',     'nature'=>'Auspicious',  'lord'=>'Mercury', 'deity'=>'Lakshmi',    'cls'=>'Subha',   'desc'=>'Pure auspiciousness; very good for all activities'       ],
        ['n'=>'Shukla',     'nature'=>'Auspicious',  'lord'=>'Moon',    'deity'=>'Parvati',    'cls'=>'Subha',   'desc'=>'Brightness and clarity; excellent for creative work'      ],
        ['n'=>'Brahma',     'nature'=>'Auspicious',  'lord'=>'Moon',    'deity'=>'Brahma',     'cls'=>'Subha',   'desc'=>'Creative power; excellent for starting new projects'      ],
        ['n'=>'Indra',      'nature'=>'Auspicious',  'lord'=>'Sun',     'deity'=>'Indra',      'cls'=>'Subha',   'desc'=>'Kingly victory; good for competitive and bold endeavours' ],
        ['n'=>'Vaidhriti',  'nature'=>'Inauspicious','lord'=>'Saturn',  'deity'=>'Mitra',      'cls'=>'Mahavisha','desc'=>'Portends loss; a very inauspicious yoga — use caution'   ],
    ];

    private static array $NAKSHATRAS = [
        ['n'=>'Ashwini',           'l'=>'Ketu',    'd'=>'Ashwini Kumaras', 'gana'=>'Deva',    'yoni'=>'Horse',    'nadi'=>'Vata',  'tattva'=>'Earth','quality'=>'Kshipra (Quick)'],
        ['n'=>'Bharani',           'l'=>'Venus',   'd'=>'Yama',            'gana'=>'Manushya','yoni'=>'Elephant', 'nadi'=>'Pitta', 'tattva'=>'Earth','quality'=>'Ugra (Fierce)'],
        ['n'=>'Krittika',          'l'=>'Sun',     'd'=>'Agni',            'gana'=>'Rakshasa','yoni'=>'Sheep',    'nadi'=>'Kapha', 'tattva'=>'Earth','quality'=>'Mishra (Mixed)'],
        ['n'=>'Rohini',            'l'=>'Moon',    'd'=>'Brahma',          'gana'=>'Manushya','yoni'=>'Serpent',  'nadi'=>'Kapha', 'tattva'=>'Earth','quality'=>'Dhruva (Fixed)'],
        ['n'=>'Mrigashira',        'l'=>'Mars',    'd'=>'Soma',            'gana'=>'Deva',    'yoni'=>'Serpent',  'nadi'=>'Pitta', 'tattva'=>'Earth','quality'=>'Mridu (Soft)'],
        ['n'=>'Ardra',             'l'=>'Rahu',    'd'=>'Rudra',           'gana'=>'Manushya','yoni'=>'Dog',      'nadi'=>'Vata',  'tattva'=>'Water','quality'=>'Tikshna (Sharp)'],
        ['n'=>'Punarvasu',         'l'=>'Jupiter', 'd'=>'Aditi',           'gana'=>'Deva',    'yoni'=>'Cat',      'nadi'=>'Vata',  'tattva'=>'Water','quality'=>'Chara (Movable)'],
        ['n'=>'Pushya',            'l'=>'Saturn',  'd'=>'Brihaspati',      'gana'=>'Deva',    'yoni'=>'Sheep',    'nadi'=>'Pitta', 'tattva'=>'Water','quality'=>'Mridu (Soft)'],
        ['n'=>'Ashlesha',          'l'=>'Mercury', 'd'=>'Nagas',           'gana'=>'Rakshasa','yoni'=>'Cat',      'nadi'=>'Kapha', 'tattva'=>'Water','quality'=>'Tikshna (Sharp)'],
        ['n'=>'Magha',             'l'=>'Ketu',    'd'=>'Pitris',          'gana'=>'Rakshasa','yoni'=>'Rat',      'nadi'=>'Kapha', 'tattva'=>'Water','quality'=>'Ugra (Fierce)'],
        ['n'=>'Purva Phalguni',    'l'=>'Venus',   'd'=>'Bhaga',           'gana'=>'Manushya','yoni'=>'Rat',      'nadi'=>'Pitta', 'tattva'=>'Water','quality'=>'Ugra (Fierce)'],
        ['n'=>'Uttara Phalguni',   'l'=>'Sun',     'd'=>'Aryaman',         'gana'=>'Manushya','yoni'=>'Cow',      'nadi'=>'Vata',  'tattva'=>'Fire', 'quality'=>'Dhruva (Fixed)'],
        ['n'=>'Hasta',             'l'=>'Moon',    'd'=>'Savitar',         'gana'=>'Deva',    'yoni'=>'Buffalo',  'nadi'=>'Vata',  'tattva'=>'Fire', 'quality'=>'Kshipra (Quick)'],
        ['n'=>'Chitra',            'l'=>'Mars',    'd'=>'Vishwakarma',     'gana'=>'Rakshasa','yoni'=>'Tiger',    'nadi'=>'Pitta', 'tattva'=>'Fire', 'quality'=>'Mridu (Soft)'],
        ['n'=>'Swati',             'l'=>'Rahu',    'd'=>'Vayu',            'gana'=>'Deva',    'yoni'=>'Buffalo',  'nadi'=>'Kapha', 'tattva'=>'Fire', 'quality'=>'Chara (Movable)'],
        ['n'=>'Vishakha',          'l'=>'Jupiter', 'd'=>'Indra-Agni',      'gana'=>'Rakshasa','yoni'=>'Tiger',    'nadi'=>'Kapha', 'tattva'=>'Fire', 'quality'=>'Mishra (Mixed)'],
        ['n'=>'Anuradha',          'l'=>'Saturn',  'd'=>'Mitra',           'gana'=>'Deva',    'yoni'=>'Deer',     'nadi'=>'Pitta', 'tattva'=>'Air',  'quality'=>'Mridu (Soft)'],
        ['n'=>'Jyeshtha',          'l'=>'Mercury', 'd'=>'Indra',           'gana'=>'Rakshasa','yoni'=>'Deer',     'nadi'=>'Vata',  'tattva'=>'Air',  'quality'=>'Tikshna (Sharp)'],
        ['n'=>'Moola',             'l'=>'Ketu',    'd'=>'Nirrti',          'gana'=>'Rakshasa','yoni'=>'Dog',      'nadi'=>'Kapha', 'tattva'=>'Air',  'quality'=>'Tikshna (Sharp)'],
        ['n'=>'Purva Ashadha',     'l'=>'Venus',   'd'=>'Apas',            'gana'=>'Manushya','yoni'=>'Monkey',   'nadi'=>'Pitta', 'tattva'=>'Air',  'quality'=>'Ugra (Fierce)'],
        ['n'=>'Uttara Ashadha',    'l'=>'Sun',     'd'=>'Vishvedevas',     'gana'=>'Manushya','yoni'=>'Mongoose', 'nadi'=>'Vata',  'tattva'=>'Air',  'quality'=>'Dhruva (Fixed)'],
        ['n'=>'Shravana',          'l'=>'Moon',    'd'=>'Vishnu',          'gana'=>'Deva',    'yoni'=>'Monkey',   'nadi'=>'Kapha', 'tattva'=>'Ether','quality'=>'Chara (Movable)'],
        ['n'=>'Dhanishta',         'l'=>'Mars',    'd'=>'Ashta Vasus',     'gana'=>'Rakshasa','yoni'=>'Lion',     'nadi'=>'Pitta', 'tattva'=>'Ether','quality'=>'Chara (Movable)'],
        ['n'=>'Shatabhisha',       'l'=>'Rahu',    'd'=>'Varuna',          'gana'=>'Rakshasa','yoni'=>'Horse',    'nadi'=>'Vata',  'tattva'=>'Ether','quality'=>'Chara (Movable)'],
        ['n'=>'Purva Bhadrapada',  'l'=>'Jupiter', 'd'=>'Aja Ekapada',     'gana'=>'Manushya','yoni'=>'Lion',     'nadi'=>'Vata',  'tattva'=>'Ether','quality'=>'Ugra (Fierce)'],
        ['n'=>'Uttara Bhadrapada', 'l'=>'Saturn',  'd'=>'Ahir Budhyana',   'gana'=>'Manushya','yoni'=>'Cow',      'nadi'=>'Pitta', 'tattva'=>'Ether','quality'=>'Dhruva (Fixed)'],
        ['n'=>'Revati',            'l'=>'Mercury', 'd'=>'Pushan',          'gana'=>'Deva',    'yoni'=>'Elephant', 'nadi'=>'Kapha', 'tattva'=>'Ether','quality'=>'Mridu (Soft)'],
    ];

    private static array $WESTERN_SIGNS = [
        ['n'=>'Aries',       's'=>'♈'],['n'=>'Taurus',      's'=>'♉'],
        ['n'=>'Gemini',      's'=>'♊'],['n'=>'Cancer',      's'=>'♋'],
        ['n'=>'Leo',         's'=>'♌'],['n'=>'Virgo',       's'=>'♍'],
        ['n'=>'Libra',       's'=>'♎'],['n'=>'Scorpio',     's'=>'♏'],
        ['n'=>'Sagittarius', 's'=>'♐'],['n'=>'Capricorn',   's'=>'♑'],
        ['n'=>'Aquarius',    's'=>'♒'],['n'=>'Pisces',      's'=>'♓'],
    ];

    private static array $VEDIC_SIGNS = [
        'Mesha','Vrishabha','Mithuna','Karka','Simha','Kanya',
        'Tula','Vrishchika','Dhanu','Makara','Kumbha','Meena',
    ];

    private static array $SIGN_LORDS = [
        'Mars','Venus','Mercury','Moon','Sun','Mercury',
        'Venus','Mars','Jupiter','Saturn','Saturn','Jupiter',
    ];

    // Vimshottari Dasha
    private static array $DASHA_LORDS = ['Ketu','Venus','Sun','Moon','Mars','Rahu','Jupiter','Saturn','Mercury'];
    private static array $DASHA_YEARS = [7, 20, 6, 10, 7, 18, 16, 19, 17];

    // Keplerian orbital elements (Meeus Ch.32/33)
    private static array $ELEMENTS = [
        'mercury' => ['L0'=>252.250906,'L1'=>149474.0722491,'a'=>0.38709831,'e0'=>0.20563175,'e1'=> 0.000020406,'w0'=> 77.456119,'w1'=> 0.1588643,'Om0'=> 48.330893,'Om1'=>-0.1254229,'i0'=> 7.004986,'i1'=>-0.0059516],
        'venus'   => ['L0'=>181.979801,'L1'=> 58519.2130302,'a'=>0.72332982,'e0'=>0.00677188,'e1'=>-0.000047766,'w0'=>131.563707,'w1'=> 0.1212060,'Om0'=> 76.679920,'Om1'=>-0.2780080,'i0'=> 3.394662,'i1'=>-0.0008568],
        'mars'    => ['L0'=>355.433275,'L1'=> 19141.6964746,'a'=>1.52371243,'e0'=>0.09340062,'e1'=> 0.000090483,'w0'=>336.060234,'w1'=> 0.4438898,'Om0'=> 49.558093,'Om1'=>-0.2949846,'i0'=> 1.849726,'i1'=>-0.0006011],
        'jupiter' => ['L0'=> 34.351519,'L1'=>  3036.3027748,'a'=>5.20248019,'e0'=>0.04853590,'e1'=> 0.000016322,'w0'=> 14.331964,'w1'=> 0.2155209,'Om0'=>100.464441,'Om1'=> 0.1766828,'i0'=> 1.303270,'i1'=>-0.0054966],
        'saturn'  => ['L0'=> 50.077444,'L1'=>  1223.5110686,'a'=>9.54149883,'e0'=>0.05550825,'e1'=>-0.000346641,'w0'=> 93.056787,'w1'=> 0.5665496,'Om0'=>113.665524,'Om1'=>-0.2566649,'i0'=> 2.488878,'i1'=>-0.0037363],
    ];
    
    /**
     * Main entry point — full chart calculation.
     */  
    public static function calculate(
        int $yr, int $mo, int $dy,
        int $hr, int $mn,
        float $utcOff, float $lat, float $lon
    ): array {
        // Local → UTC with date rollover
        $utHr = ($hr + $mn / 60.0) - $utcOff;
        $baseTs = gmmktime(0, 0, 0, $mo, $dy, $yr);
        $utcTs  = $baseTs + (int)round($utHr * 3600);
        $adjYr  = (int)gmdate('Y', $utcTs);
        $adjMo  = (int)gmdate('n', $utcTs);
        $adjDy  = (int)gmdate('j', $utcTs);
        $adjUtHr= (int)gmdate('G', $utcTs)
                + (int)gmdate('i', $utcTs) / 60.0
                + (int)gmdate('s', $utcTs) / 3600.0;

        $jd   = self::julianDay($adjYr, $adjMo, $adjDy, $adjUtHr);
        $ayan = self::lahiriAyanamsa($jd);

        // Angles
        $angles   = self::computeAngles($jd, $lat, $lon);
        $ascTrop  = $angles['asc'];
        $ascSider = self::n360($ascTrop - $ayan);
        $ascSignIdx = (int)floor($ascSider / 30.0);

        // Whole-sign house map
        $houseSign = [];
        for ($h = 0; $h < 12; $h++) {
            $houseSign[$h] = ($ascSignIdx + $h) % 12;
        }

        // Planets
        $planets = [
            'sun'     => self::computePlanet($jd, $ayan, 'sun',     false),
            'moon'    => self::computePlanet($jd, $ayan, 'moon',    false),
            'mercury' => self::computePlanet($jd, $ayan, 'mercury', null),
            'venus'   => self::computePlanet($jd, $ayan, 'venus',   null),
            'mars'    => self::computePlanet($jd, $ayan, 'mars',    null),
            'jupiter' => self::computePlanet($jd, $ayan, 'jupiter', null),
            'saturn'  => self::computePlanet($jd, $ayan, 'saturn',  null),
            'rahu'    => self::computePlanet($jd, $ayan, 'rahu',    true),
            'ketu'    => self::computePlanet($jd, $ayan, 'ketu',    true),
        ];

        // Sunrise / Sunset
        $ss = self::sunriseSunset($yr, $mo, $dy, $lat, $lon, $utcOff);

        // Tithi / Karana at input time, sunrise, sunset
        $tk     = self::computeTithiKarana($jd);
        $tkRise = null;
        $tkSet  = null;
        if (!$ss['polar'] && $ss['rise'] !== null) {
            $jdRise = self::julianDay($yr, $mo, $dy, $ss['rise'] - $utcOff);
            $tkRise = self::computeTithiKarana($jdRise);
        }
        if (!$ss['polar'] && $ss['set'] !== null) {
            $jdSet  = self::julianDay($yr, $mo, $dy, $ss['set'] - $utcOff);
            $tkSet  = self::computeTithiKarana($jdSet);
        }

        // Panchanga
        $pancha = self::computePanchanga($jd, $ayan, $yr, $mo, $dy, $utcOff);

        // Sun equatorial
        $sunEq = self::sunEquatorial($jd);

        // Dasha
        $moonSider = self::n360($planets['moon']['trop'] - $ayan);
        $dasha = self::getDashaMd($moonSider);

        return compact(
            'jd','ayan','angles','ascTrop','ascSider','ascSignIdx',
            'houseSign','planets','ss','tk','tkRise','tkSet',
            'pancha','sunEq','dasha'
        );
    }

    // ── Public accessors for static data ─────────────────────────

    public static function getNakshatras(): array { return self::$NAKSHATRAS; }
    public static function getVedicSigns():  array { return self::$VEDIC_SIGNS; }
    public static function getWesternSigns(): array { return self::$WESTERN_SIGNS; }

    // ══════════════════════════════════════════════════════════════
    //  JULIAN DAY  (Meeus Ch.7)
    // ══════════════════════════════════════════════════════════════
    public static function julianDay(int $yr, int $mo, int $dy, float $utHr): float
    {
        if ($mo <= 2) { $yr--; $mo += 12; }
        $A = (int)floor($yr / 100);
        $B = 2 - $A + (int)floor($A / 4);
        return (int)floor(365.25 * ($yr + 4716))
             + (int)floor(30.6001 * ($mo + 1))
             + $dy + $utHr / 24.0 + $B - 1524.5;
    }

    // ══════════════════════════════════════════════════════════════
    //  LAHIRI AYANAMSA  (IAU 1976 precession, Rashtriya Panchang seed)
    // ══════════════════════════════════════════════════════════════
    public static function lahiriAyanamsa(float $jd): float
    {
        $T     = ($jd - 2451545.0) / 36525.0;
        $years = $T * 100.0;
        $precArcSec = 50.2910 * $years
                    + 0.022   * $T * $T
                    - 0.0003  * $T * $T * $T;
        return 23.853722 + $precArcSec / 3600.0;
    }

    // ══════════════════════════════════════════════════════════════
    //  MOON LONGITUDE  (Meeus Ch.47 full series, ±0.3°)
    // ══════════════════════════════════════════════════════════════
    public static function moonLongitude(float $jd): float
    {
        $T  = ($jd - 2451545.0) / 36525.0;
        $Lp = self::n360(218.3164477 + 481267.88123421*$T - 0.0015786*$T*$T + $T*$T*$T/538841.0);
        $D  = self::n360(297.8501921 + 445267.1114034 *$T - 0.0018819*$T*$T + $T*$T*$T/545868.0);
        $M  = self::n360(357.5291092 + 35999.0502909  *$T - 0.0001536*$T*$T);
        $Mp = self::n360(134.9633964 + 477198.8675055 *$T + 0.0087414*$T*$T + $T*$T*$T/69699.0);
        $F  = self::n360(93.2720950  + 483202.0175233 *$T - 0.0036539*$T*$T);
        $A1 = self::n360(119.75 + 131.849*$T);
        $A2 = self::n360(53.09 + 479264.290*$T);
        $E  = 1.0 - 0.002516*$T - 0.0000074*$T*$T;
        $E2 = $E * $E;

        static $terms = [
            [0,0,1,0,6288774],[2,0,-1,0,1274027],[2,0,0,0,658314],[0,0,2,0,213618],
            [0,1,0,0,-185116],[0,0,0,2,-114332],[2,0,-2,0,58793],[2,-1,-1,0,57066],
            [2,0,1,0,53322],[2,-1,0,0,45758],[0,1,-1,0,-40923],[1,0,0,0,-34720],
            [0,1,1,0,-30383],[2,0,0,-2,15327],[0,0,1,-2,10980],[4,0,-1,0,10675],
            [0,0,3,0,10034],[4,0,-2,0,8548],[2,1,-1,0,-7888],[2,1,0,0,-6766],
            [1,0,-1,0,-5163],[1,1,0,0,4987],[2,-1,1,0,4036],[2,0,2,0,3994],
            [4,0,0,0,3861],[2,0,-3,0,3665],[0,1,-2,0,-2689],[2,-1,-2,0,2390],
            [1,0,1,0,-2348],[2,-2,0,0,2236],[0,1,2,0,-2120],[0,2,0,0,-2069],
            [2,-2,-1,0,2048],[2,0,1,-2,-1773],[2,0,0,2,-1595],[4,-1,-1,0,1215],
            [0,0,2,2,-1110],[3,0,-1,0,-892],[2,1,1,0,-810],[4,-1,-2,0,759],
            [0,2,-1,0,-713],[2,2,-1,0,-700],[2,1,-2,0,691],[4,0,1,0,549],
            [0,0,4,0,537],[4,-1,0,0,520],[1,0,-2,0,-487],[0,0,2,-2,-381],
            [1,1,1,0,351],[3,0,-2,0,-340],[4,0,-3,0,330],[2,-1,2,0,327],
            [0,2,1,0,-323],[1,1,-1,0,299],[2,0,3,0,294],
        ];

        $Sl = 0.0;
        foreach ($terms as [$d,$m,$mp,$f,$c]) {
            $cf = (float)$c;
            if (abs($m) === 1) $cf *= $E;
            if (abs($m) === 2) $cf *= $E2;
            $Sl += $cf * sin(self::r($d*$D + $m*$M + $mp*$Mp + $f*$F));
        }
        $Sl += 3958.0*sin(self::r($A1))
             + 1962.0*sin(self::r($Lp - $F))
             + 318.0 *sin(self::r($A2));
        return self::n360($Lp + $Sl / 1e6);
    }

    // ══════════════════════════════════════════════════════════════
    //  RAHU (Mean ascending lunar node, always retrograde)
    // ══════════════════════════════════════════════════════════════
    public static function rahuLongitude(float $jd): float
{
    $T  = ($jd - 2451545.0) / 36525.0;
    $T2 = $T * $T;
    $T3 = $T2 * $T;

    // Mean node (Meeus Ch.47)
    $Om = self::n360(125.0445479 - 1934.1362608*$T + 0.0020754*$T2 + $T3/467441.0);

    // True node corrections (Meeus Ch.47, principal periodic terms)
    $D  = self::n360(297.8501921 + 445267.1114034*$T - 0.0018819*$T2 + $T3/545868.0);
    $M  = self::n360(357.5291092 + 35999.0502909 *$T - 0.0001536*$T2);
    $Mp = self::n360(134.9633964 + 477198.8675055*$T + 0.0087414*$T2 + $T3/69699.0);
    $F  = self::n360(93.2720950  + 483202.0175233*$T - 0.0036539*$T2);

    $corr =
        -1.4979 * sin(self::r(2.0*($D - $F)))
        -0.1500 * sin(self::r($M))
        -0.1226 * sin(self::r(2.0*$D))
        +0.1176 * sin(self::r(2.0*$F))
        -0.0801 * sin(self::r(2.0*($Mp - $F)));

    return self::n360($Om + $corr);
}

    // ══════════════════════════════════════════════════════════════
    //  SUN LONGITUDE  (Meeus Ch.27 apparent, ~0.01° accuracy)
    // ══════════════════════════════════════════════════════════════
    public static function sunLongitude(float $jd): float
    {
        $T  = ($jd - 2451545.0) / 36525.0;
        $T2 = $T * $T;
        $T3 = $T2 * $T;
        $L0 = self::n360(280.46646 + 36000.76983*$T + 0.0003032*$T2);
        $M  = self::n360(357.52911 + 35999.05029*$T - 0.0001537*$T2 - 0.00000048*$T3);
        $Mr = self::r($M);
        $C  = (1.9146 - 0.004817*$T - 0.000014*$T2) * sin($Mr)
            + (0.019993 - 0.000101*$T) * sin(2.0*$Mr)
            + 0.000290 * sin(3.0*$Mr)
            + 0.0000075 * sin(4.0*$Mr);
        $sunTrue = $L0 + $C;
        $omega   = self::n360(125.04452 - 1934.136261*$T + 0.0020708*$T2 + $T3/450000.0);
        $appLon  = $sunTrue - 0.00569 - 0.00478 * sin(self::r($omega));
        return self::n360($appLon);
    }

    public static function sunEquatorial(float $jd): array
    {
        $T   = ($jd - 2451545.0) / 36525.0;
        $eps = 23.439291111
             - 0.013004167  * $T
             - 0.0000001639 * $T * $T
             + 0.0000005036 * $T * $T * $T;
        $lon  = self::sunLongitude($jd);
        $lonR = self::r($lon);
        $epsR = self::r($eps);
        $ra   = atan2(cos($epsR)*sin($lonR), cos($lonR)) / self::DEG;
        $dec  = asin(sin($epsR)*sin($lonR)) / self::DEG;
        return ['ra' => self::n360($ra), 'dec' => $dec, 'lon' => $lon];
    }

    // ══════════════════════════════════════════════════════════════
    //  PLANETARY LONGITUDES  (Meeus Ch.32/33 Keplerian + perturbations)
    // ══════════════════════════════════════════════════════════════
    public static function planetLongitude(float $jd, string $planet): float
    {
        $T  = ($jd - 2451545.0) / 36525.0;
        $el = self::$ELEMENTS[$planet];

        $L   = self::n360($el['L0'] + $el['L1']*$T);
        $ww  = $el['w0']  + $el['w1']  * $T;
        $Om  = $el['Om0'] + $el['Om1'] * $T;
        $inc = self::r($el['i0'] + $el['i1'] * $T);
        $e   = $el['e0']  + $el['e1']  * $T;
        $a   = $el['a'];

        $M_deg = self::n360($L - $ww);
        $E     = self::solveKepler($M_deg, $e);
        $nu    = 2.0 * atan2(sqrt(1.0+$e)*sin($E/2.0), sqrt(1.0-$e)*cos($E/2.0));
        $rv    = $a * (1.0 - $e*cos($E));

        $omR = self::r($Om);
        $wR  = self::r($ww - $Om);
        $u   = $nu + $wR;
        $xh  = $rv * (cos($omR)*cos($u) - sin($omR)*sin($u)*cos($inc));
        $yh  = $rv * (sin($omR)*cos($u) + cos($omR)*sin($u)*cos($inc));

        // Earth heliocentric position
        $Me   = self::n360(357.52911 + 35999.05029*$T - 0.0001537*$T*$T);
        $MeR  = self::r($Me);
        $r_e  = 1.000001018 * (1.0
              - 0.01671123*cos($MeR)
              - 0.000139  *cos(2.0*$MeR)
              - 0.000014  *cos(3.0*$MeR)
              + 0.0000003 *cos(4.0*$MeR));
        $sLon = self::r(self::sunLongitude($jd));
        $xe   = $r_e * cos($sLon + M_PI);
        $ye   = $r_e * sin($sLon + M_PI);

        $lon = atan2($yh - $ye, $xh - $xe) / self::DEG;
        $lon = self::n360($lon);

        // Jupiter / Saturn mutual perturbations (Meeus Ch.33)
        if ($planet === 'jupiter' || $planet === 'saturn') {
            $Mj  = self::n360(20.9  + 0.071023 * ($jd - 2451545.0));
            $Ms  = self::n360(317.0 + 0.028441 * ($jd - 2451545.0));
            $MjR = self::r($Mj);
            $MsR = self::r($Ms);
            if ($planet === 'jupiter') {
                $lon += (  0.3314*sin(2.0*$MsR - 5.0*$MjR - self::r(67.6))
                         - 0.0390*sin($MsR - 2.0*$MjR + self::r(76.0))
                         + 0.0318*sin($MsR - 3.0*$MjR + self::r(13.0))
                         - 0.0185*sin($MsR  + self::r(100.0))
                         - 0.0143*sin(2.0*$MjR)) / 3600.0;
            } else {
                $lon += (- 0.8138*sin(2.0*$MsR - 4.0*$MjR - self::r(68.0))
                         + 0.2073*sin(2.0*$MsR - 5.0*$MjR - self::r(67.6))
                         - 0.0924*sin(2.0*$MsR - 3.0*$MjR)
                         + 0.0462*sin($MsR - self::r(56.0))
                         - 0.0402*sin($MsR + $MjR - self::r(120.0))) / 3600.0;
            }
            $lon = self::n360($lon);
        }
        return $lon;
    }

    // ══════════════════════════════════════════════════════════════
    //  ASCENDANT & ANGLES  (Meeus Ch.25 RAMC method)
    // ══════════════════════════════════════════════════════════════
    public static function computeAngles(float $jd, float $lat, float $lon): array
    {
        $T   = ($jd - 2451545.0) / 36525.0;
        $eps = 23.439291111
             - 0.013004167  * $T
             - 0.0000001639 * $T * $T
             + 0.0000005036 * $T * $T * $T;

        $gmst = self::n360(
            280.46061837
            + 360.98564736629 * ($jd - 2451545.0)
            + 0.000387933 * $T * $T
            - $T * $T * $T / 38710000.0
        );
        $lst   = self::n360($gmst + $lon);
        $ramc  = $lst;
        $raMC_r = self::r($ramc);
        $epsR   = self::r($eps);

        // MC
        $mc = atan2(sin($raMC_r), cos($raMC_r) * cos($epsR)) / self::DEG;
        $mc = self::n360($mc);

        // Ascendant
        $latR = self::r($lat);
        $num  = -cos($raMC_r);
        $den  = sin($epsR)*tan($latR) + cos($epsR)*sin($raMC_r);
        $asc  = atan2($num, $den) / self::DEG;
        $asc  = self::n360($asc);

        // Quadrant correction
        $expected = self::n360($ramc + 90.0);
        $diff = $asc - $expected;
        if ($diff >  180.0) $diff -= 360.0;
        if ($diff < -180.0) $diff += 360.0;
        if (abs($diff) > 90.0) $asc = self::n360($asc + 180.0);

        $desc = self::n360($asc + 180.0);
        $ic   = self::n360($mc  + 180.0);

        return compact('asc','desc','mc','ic','lst','eps');
    }

    // ══════════════════════════════════════════════════════════════
    //  TITHI & KARANA
    // ══════════════════════════════════════════════════════════════
    public static function computeTithiKarana(float $jd): array
    {
        $moonLon = self::moonLongitude($jd);
        $sunLon  = self::sunLongitude($jd);
        $elong   = self::n360($moonLon - $sunLon);

        $tithiIndex = (int)floor($elong / 12.0);
        $tithiProg  = fmod($elong, 12.0) / 12.0;
        $tithi      = self::$TITHIS[$tithiIndex];

        $karanaSlotRaw = (int)floor($elong / 6.0);  // 0..59
        $karanaProg    = fmod($elong, 6.0) / 6.0;

        if ($karanaSlotRaw === 0) {
            $karana = self::$KARANA_FIXED[0];           // Kimstughna
        } elseif ($karanaSlotRaw <= 56) {
            $karana = self::$KARANA_CYCLE[($karanaSlotRaw - 1) % 7];
        } elseif ($karanaSlotRaw === 57) {
            $karana = self::$KARANA_FIXED[1];           // Shakuni
        } elseif ($karanaSlotRaw === 58) {
            $karana = self::$KARANA_FIXED[2];           // Chatushpada
        } else {
            $karana = self::$KARANA_FIXED[3];           // Naga
        }

        $karanaSlot = $karanaSlotRaw + 1;   // 1-based display
        $tithiHalf  = ($karanaSlotRaw % 2 === 0) ? 'First Half' : 'Second Half';

        return compact(
            'elong','tithi','tithiIndex','tithiProg',
            'karana','karanaSlot','karanaProg','tithiHalf',
            'moonLon','sunLon'
        );
    }

    // ══════════════════════════════════════════════════════════════
    //  PANCHANGA — Vara, Nakshatra, Yoga
    // ══════════════════════════════════════════════════════════════
    public static function computePanchanga(
        float $jd, float $ayan,
        int $yr, int $mo, int $dy, float $utcOff
    ): array {
        // Vara: weekday at local noon
        $varaIdx = ((int)floor($jd + $utcOff/24.0 + 1.5)) % 7;
        $vara    = self::$VARAS[$varaIdx];

        // Moon nakshatra (sidereal)
        $moonSider = self::n360(self::moonLongitude($jd) - $ayan);
        $nakSz     = 360.0 / 27.0;
        $nakIdx    = (int)floor($moonSider / $nakSz);
        $nakProg   = fmod($moonSider, $nakSz) / $nakSz;
        $nakPada   = (int)floor($nakProg * 4.0) + 1;
        $moonNak   = self::$NAKSHATRAS[$nakIdx];

        // Yoga
        $sunSider = self::n360(self::sunLongitude($jd) - $ayan);
        $yogaSum  = self::n360($sunSider + $moonSider);
        $yogaIdx  = (int)floor($yogaSum / $nakSz) % 27;
        $yogaProg = fmod($yogaSum, $nakSz) / $nakSz;
        $yoga     = self::$YOGAS[$yogaIdx];

        return compact(
            'vara','varaIdx',
            'moonNak','nakIdx','nakProg','nakPada',
            'yoga','yogaIdx','yogaProg',
            'moonSider','sunSider','yogaSum'
        );
    }

    // ══════════════════════════════════════════════════════════════
    //  VIMSHOTTARI DASHA BALANCE
    // ══════════════════════════════════════════════════════════════
    public static function getDashaMd(float $moonSiderLon): array
    {
        $nakSz    = 360.0 / 27.0;
        $nakIdx   = (int)floor($moonSiderLon / $nakSz);
        $nakProg  = fmod($moonSiderLon, $nakSz) / $nakSz;
        $lords    = self::$DASHA_LORDS;
        $years    = self::$DASHA_YEARS;
        // Each nakshatra maps to one of 9 lords cyclically
        $lordIdx  = $nakIdx % 9;
        $lord     = $lords[$lordIdx];
        $lordYrs  = $years[$lordIdx];
        $elapsed  = $nakProg * $lordYrs;
        $remaining= $lordYrs - $elapsed;
        $yrs      = (int)floor($remaining);
        $mosFrac  = ($remaining - $yrs) * 12.0;
        $mos      = (int)floor($mosFrac);
        $days     = (int)round(($mosFrac - $mos) * 30.0);
        return compact('lord','remaining','yrs','mos','days','lordYrs');
    }

    // ══════════════════════════════════════════════════════════════
    //  SUNRISE / SUNSET  (Meeus Ch.15)
    // ══════════════════════════════════════════════════════════════
    public static function sunriseSunset(
        int $yr, int $mo, int $dy,
        float $lat, float $lon, float $utcOff
    ): array {
        $jd  = self::julianDay($yr, $mo, $dy, 12.0 - $utcOff);
        $eq  = self::sunEquatorial($jd);
        $dec = $eq['dec'];

        $latR = self::r($lat);
        $decR = self::r($dec);
        $cosH = (cos(self::r(90.833)) - sin($latR)*sin($decR))
              / (cos($latR)*cos($decR));

        if ($cosH >  1.0) return ['rise'=>null,'set'=>null,'polar'=>'no_rise','dayLength'=>0.0];
        if ($cosH < -1.0) return ['rise'=>null,'set'=>null,'polar'=>'no_set', 'dayLength'=>24.0];

        $H  = acos($cosH) / self::DEG;
        $T  = ($jd - 2451545.0) / 36525.0;
        $L0 = self::n360(280.46646 + 36000.76983*$T);
        $M  = self::n360(357.52911 + 35999.05029*$T);
        $eps = 23.439291111 - 0.013004167*$T;
        $y   = tan(self::r($eps/2.0)) ** 2;
        $eot = 4.0 * ($y*sin(self::r(2.0*$L0))
                    - 2.0*0.016708634*sin(self::r($M))
                    + 4.0*0.016708634*$y*sin(self::r($M))*cos(self::r(2.0*$L0))
                    - 0.5*$y*$y*sin(self::r(4.0*$L0))
                    - 1.25*0.016708634*0.016708634*sin(self::r(2.0*$M)));

        $lngHour = $lon / 15.0;
        return [
            'rise'      => self::normalHour(12.0 - $H/15.0 - $lngHour - $eot/60.0 + $utcOff),
            'set'       => self::normalHour(12.0 + $H/15.0 - $lngHour - $eot/60.0 + $utcOff),
            'polar'     => null,
            'dayLength' => $H * 2.0 / 15.0,
        ];
    }

    // ══════════════════════════════════════════════════════════════
    //  MONTHLY MASA PANCHANGA
    // ══════════════════════════════════════════════════════════════
    public static function buildMasaData(
        int $year, int $vedMon, float $lat, float $lon, float $utcOff
    ): array {
        // Vedic month → civil month map
        $civilMonthMap = [3,4,5,6,7,8,9,10,11,12,1,2];
        $civilMon = $civilMonthMap[$vedMon - 1];
        $civYear  = ($civilMon <= 2) ? $year + 1 : $year;
        $daysInMonth = (int)(new \DateTime("$civYear-$civilMon-01"))->format('t');

        $VARA_SHORT = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        $MASA_NAMES = ['Chaitra','Vaishakha','Jyeshtha','Ashadha','Shravana',
                       'Bhadrapada','Ashwin','Kartik','Margashirsha','Pausha','Magha','Phalguna'];

        // Moon sign changes for the month
        $moonChanges = self::findMoonSignChanges($civYear, $civilMon, $lat, $lon, $utcOff);
        $moonChangesByDay = [];
        foreach ($moonChanges as $ch) {
            $moonChangesByDay[$ch['day']][] = $ch;
        }

        $rows = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $ss    = self::sunriseSunset($civYear, $civilMon, $d, $lat, $lon, $utcOff);
            $riseHr= (!$ss['polar'] && $ss['rise'] !== null) ? $ss['rise'] : null;
            $setHr = (!$ss['polar'] && $ss['set']  !== null) ? $ss['set']  : null;

            $jdRef = ($riseHr !== null)
                   ? self::julianDay($civYear, $civilMon, $d, $riseHr - $utcOff)
                   : self::julianDay($civYear, $civilMon, $d, 12.0    - $utcOff);

            $ayan   = self::lahiriAyanamsa($jdRef);
            $tk     = self::computeTithiKarana($jdRef);
            $pancha = self::computePanchanga($jdRef, $ayan, $civYear, $civilMon, $d, $utcOff);

            // Samapti Kaal
            $tithiEnd  = self::findNextCrossing($jdRef, $utcOff, 12.0,        [$civYear,$civilMon,$d,'elong'],   2.0);
            $karanaEnd = self::findNextCrossing($jdRef, $utcOff, 6.0,         [$civYear,$civilMon,$d,'elong'],   2.0);
            $nakEnd    = self::findNextCrossing($jdRef, $utcOff, 360.0/27.0,  [$civYear,$civilMon,$d,'nakSider'],2.0);
            $yogaEnd   = self::findNextCrossing($jdRef, $utcOff, 360.0/27.0,  [$civYear,$civilMon,$d,'yogaSum'], 2.0);

            $dow = (int)(new \DateTime("$civYear-$civilMon-$d"))->format('w');

            $isAmavasya = ($tk['tithi']['paksha'] === 'Krishna' && $tk['tithi']['num'] === 15);
            $isPurnima  = ($tk['tithi']['paksha'] === 'Shukla'  && $tk['tithi']['num'] === 15);
            $isEkadashi = ($tk['tithi']['num'] === 11);

            $riseStr = $riseHr !== null ? substr(self::decToHMS($riseHr), 0, 5) : '—';
            $setStr  = $setHr  !== null ? substr(self::decToHMS($setHr),  0, 5) : '—';

            // Rashi Pravesh
            $rashiPravesh = '';
            if (!empty($moonChangesByDay[$d])) {
                $parts = [];
                foreach ($moonChangesByDay[$d] as $ch) {
                    $parts[] = self::$VEDIC_SIGNS[$ch['sign']] . ' ' . $ch['time'];
                }
                $rashiPravesh = implode(', ', $parts);
            }

            $rows[] = [
                'day'           => $d,
                'dow'           => $dow,
                'varaShort'     => $VARA_SHORT[$dow],
                'paksha'        => $tk['tithi']['paksha'],
                'tithiName'     => $tk['tithi']['n'],
                'tithiNum'      => $tk['tithi']['num'],
                'tithiSamapti'  => self::fmtSamaptiLocal($tithiEnd,  $riseHr),
                'karanaName'    => $tk['karana']['n'],
                'karanaSamapti' => self::fmtSamaptiLocal($karanaEnd, $riseHr),
                'nakName'       => $pancha['moonNak']['n'],
                'nakSamapti'    => self::fmtSamaptiLocal($nakEnd,    $riseHr),
                'yogaName'      => $pancha['yoga']['n'],
                'yogaNature'    => $pancha['yoga']['nature'],
                'yogaSamapti'   => self::fmtSamaptiLocal($yogaEnd,   $riseHr),
                'riseStr'       => $riseStr,
                'setStr'        => $setStr,
                'rashiPravesh'  => $rashiPravesh,
                'isAmavasya'    => $isAmavasya,
                'isPurnima'     => $isPurnima,
                'isEkadashi'    => $isEkadashi,
            ];
        }

        $shuklaCount  = count(array_filter($rows, fn($r) => $r['paksha'] === 'Shukla'));
        $krishnaCount = count($rows) - $shuklaCount;

        return [
            'rows'          => $rows,
            'monthName'     => $MASA_NAMES[$vedMon - 1] . ' ' . $year,
            'shuklaCount'   => $shuklaCount,
            'krishnaCount'  => $krishnaCount,
        ];
    }

    /**
     * Precise JD of the new moon (Sun–Moon conjunction) that began the lunation
     * containing $jdRef, whose Moon–Sun elongation is $elong (0–360°).
     * Newton refinement on the true elongation (≈12.19°/day relative motion)
     * removes the ~1-day error of a linear estimate near a Sankranti boundary.
     */
    public static function newMoonBefore(float $jdRef, float $elong): float
    {
        $nm = $jdRef - $elong / 12.19;
        for ($k = 0; $k < 5; $k++) {
            $e = self::n360(self::moonLongitude($nm) - self::sunLongitude($nm));
            if ($e > 180.0) $e -= 360.0;
            $nm -= $e / 12.19;
        }
        return $nm;
    }

    /**
     * Purnimanta (North-Indian) lunar-month index (0 = Chaitra) for a date.
     * Month is named from the Sun's sidereal sign at the lunation's new moon;
     * Krishna paksha takes the next month's name (Purnimanta convention).
     */
    public static function purnimantaMasaIdx(float $jdRef, float $elong, string $paksha): int
    {
        $nm      = self::newMoonBefore($jdRef, $elong);
        $sunSign = (int)floor(self::n360(self::sunLongitude($nm) - self::lahiriAyanamsa($nm)) / 30.0);
        $amanta  = ($sunSign + 1) % 12;                       // Meena → Chaitra
        return ($paksha === 'Krishna') ? ($amanta + 1) % 12 : $amanta;
    }

     public static function getEkadashiYear(
    int $year, float $lat, float $lon, float $utcOff
): array {
    $ekadashis = [];
    $ekNames   = self::getEkadashiNames();

    // Scan every civil day of the year. An Ekadashi vrat day is one where the
    // Ekadashi tithi (11) prevails at sunrise. The lunar month is named in the
    // Purnimanta (North-Indian) convention from the Sun's sidereal sign at the
    // NEW MOON that began the lunation — so names never drift across a Sankranti.
    $start    = new \DateTime("$year-01-01");
    $lastKey  = '';
    $lastJd   = -1e9;

    for ($i = 0; $i < 366; $i++) {
        $dt = clone $start; $dt->modify("+{$i} days");
        if ((int)$dt->format('Y') !== $year) break;
        $y = (int)$dt->format('Y'); $m = (int)$dt->format('n'); $d = (int)$dt->format('j');

        $ss     = self::sunriseSunset($y, $m, $d, $lat, $lon, $utcOff);
        $riseHr = (!$ss['polar'] && $ss['rise'] !== null) ? $ss['rise'] : 6.0;
        $jdRef  = self::julianDay($y, $m, $d, $riseHr - $utcOff);

        $tk = self::computeTithiKarana($jdRef);
        if ($tk['tithi']['num'] !== 11) continue;
        $paksha = $tk['tithi']['paksha'];

        // Purnimanta month from the precise new-moon Sun sign
        $purnimIdx = self::purnimantaMasaIdx($jdRef, $tk['elong'], $paksha);
        $vedMonIdx = $purnimIdx + 1;
        $key       = $paksha . '_' . $vedMonIdx;

        // Skip the second sunrise of a tithi-Vriddhi (Ekadashi on two sunrises)
        if ($key === $lastKey && ($jdRef - $lastJd) < 3.0) continue;
        $lastKey = $key; $lastJd = $jdRef;

        $ayan     = self::lahiriAyanamsa($jdRef);
        $pancha   = self::computePanchanga($jdRef, $ayan, $y, $m, $d, $utcOff);
        $tithiEnd = self::findNextCrossing($jdRef, $utcOff, 12.0, [$y, $m, $d, 'elong'], 2.0);
        $ekInfo   = $ekNames[$key] ?? ['name'=>'Ekadashi','nameHi'=>'एकादशी'];

        $ekadashis[] = [
            'name'        => $ekInfo['name'],
            'nameHi'      => $ekInfo['nameHi'],
            'paksha'      => $paksha,
            'vedMonth'    => self::MASA_NAMES[$purnimIdx],
            'vedMonthNum' => $vedMonIdx,
            'date'        => sprintf('%04d-%02d-%02d', $y, $m, $d),
            'startTime'   => $ss['rise'] !== null ? self::decToHMS($ss['rise']) : '06:00',
            'endTime'     => $tithiEnd ? self::fmtSamaptiLocal($tithiEnd, $ss['rise']) : '—',
            'tithi'       => 11,
            'tithiLord'   => 'Vishnu',
            'yoga'        => $pancha['yoga']['n'],
            'nakshatra'   => $pancha['moonNak']['n'],
            'nakLord'     => $pancha['moonNak']['l'],
            'significance'=> $ekInfo['significance'] ?? '',
            'rituals'     => $ekInfo['rituals']       ?? [],
            'mantra'      => $ekInfo['mantra']        ?? 'ॐ नमो भगवते वासुदेवाय',
            'auspTime'    => $ekInfo['auspTime']      ?? 'Sunrise to Dvadashi sunrise',
        ];
    }

    return $ekadashis;
}

public const MASA_NAMES = [
    'Chaitra','Vaishakha','Jyeshtha','Ashadha','Shravana',
    'Bhadrapada','Ashwin','Kartik','Margashirsha','Pausha','Magha','Phalguna',
];



private static function getEkadashiNames(): array {
    return [
        'Krishna_1' => ['name'=>'Papmochani Ekadashi','nameHi'=>'पापमोचनी एकादशी','significance'=>'Remover of all sins — known and unknown.','rituals'=>['Observe full fast','Vishnu Shodashopachar Puja','Listen to Katha'],'mantra'=>'ॐ नमो भगवते वासुदेवाय','auspTime'=>'Sunrise to Dvadashi sunrise'],
        'Shukla_1'  => ['name'=>'Kamada Ekadashi',   'nameHi'=>'कामदा एकादशी',    'significance'=>'Fulfills all desires.','rituals'=>['Observe full fast','Visit Vishnu temple','Chant Vishnu Sahasranama'],'mantra'=>'ॐ नमो भगवते वासुदेवाय','auspTime'=>'Sunrise to Dvadashi sunrise'],
        'Krishna_2' => ['name'=>'Varuthini Ekadashi', 'nameHi'=>'वरूथिनी एकादशी', 'significance'=>'Grants liberation from sins, equals 10,000 years of penance.','rituals'=>['Observe Ekadashi fast','Worship Vamana Vishnu','Donate generously'],'mantra'=>'ॐ नमो भगवते वासुदेवाय','auspTime'=>'Sunrise to Dvadashi sunrise'],
        'Shukla_2'  => ['name'=>'Mohini Ekadashi',   'nameHi'=>'मोहिनी एकादशी',  'significance'=>'Frees from illusion of the world.','rituals'=>['Observe full fast','Worship Vishnu in Mohini form','Chant on Tulasi mala'],'mantra'=>'ॐ नमो भगवते वासुदेवाय','auspTime'=>'Sunrise to Dvadashi'],
        'Krishna_3' => ['name'=>'Apara Ekadashi',    'nameHi'=>'अपरा एकादशी',    'significance'=>'Grants boundless merit, destroys gravest sins.','rituals'=>['Observe Ekadashi fast','Read Vishnu Sahasranama','Feed the poor'],'mantra'=>'ॐ नमो भगवते वासुदेवाय','auspTime'=>'Sunrise to Dvadashi sunrise'],
        'Shukla_3'  => ['name'=>'Nirjala Ekadashi',  'nameHi'=>'निर्जला एकादशी', 'significance'=>'Most austere — no water. Earns merit of all 24 Ekadashis combined.','rituals'=>['Complete Nirjala fast — no food or water','Worship Vishnu with Tulasi','Donate water pots with fruits','Chant Vishnu Sahasranama 108 times'],'mantra'=>'ॐ नमो भगवते वासुदेवाय | ॐ विष्णवे नमः','auspTime'=>'Entire Ekadashi day is continuous worship'],
        'Krishna_4' => ['name'=>'Yogini Ekadashi',   'nameHi'=>'योगिनी एकादशी',  'significance'=>'Heals physical and spiritual ailments.','rituals'=>['Observe Ekadashi fast','Listen to Yogini story','Serve the sick'],'mantra'=>'ॐ नमो भगवते वासुदेवाय','auspTime'=>'Sunrise to Dvadashi sunrise'],
        'Shukla_4'  => ['name'=>'Devshayani Ekadashi','nameHi'=>'देवशयनी एकादशी','significance'=>'Vishnu enters Yoga Nidra — Chaturmasya begins.','rituals'=>['Observe Ekadashi fast with devotion','Worship sleeping Vishnu','Begin 4-month spiritual discipline','Light Akhanda Dipa'],'mantra'=>'ॐ नमो भगवते वासुदेवाय','auspTime'=>'Evening and entire night — keep Akhanda Dipa burning'],
        'Krishna_5' => ['name'=>'Kamika Ekadashi',   'nameHi'=>'कामिका एकादशी',  'significance'=>'Tulasi worship especially meritorious.','rituals'=>['Observe Ekadashi fast','Special Tulasi puja','Chant on Tulasi mala','Read Bhagavata'],'mantra'=>'ॐ नमो भगवते वासुदेवाय | तुलस्यै नमः','auspTime'=>'Sunrise — begin with Tulasi puja'],
        'Shukla_5'  => ['name'=>'Putrada Ekadashi',  'nameHi'=>'पुत्रदा एकादशी', 'significance'=>'Grants virtuous offspring. Powerful in Shravana month.','rituals'=>['Observe Ekadashi fast','Worship Vishnu for progeny','Chant Santana Gopala mantra'],'mantra'=>'ॐ नमो भगवते वासुदेवाय','auspTime'=>'Sunrise to Dvadashi sunrise'],
        'Krishna_6' => ['name'=>'Aja Ekadashi',      'nameHi'=>'अजा एकादशी',     'significance'=>'Liberates from birth-death cycle. Frees ancestral souls.','rituals'=>['Observe Ekadashi fast','Perform Pitru Tarpana','Worship Vishnu'],'mantra'=>'ॐ नमो भगवते वासुदेवाय','auspTime'=>'Sunrise — Pitru Tarpana in afternoon'],
        'Shukla_6'  => ['name'=>'Parsva Ekadashi',   'nameHi'=>'पार्श्व एकादशी', 'significance'=>'Vishnu turns sides in Yoga Nidra. Vamana Jayanti nearby.','rituals'=>['Observe Ekadashi fast','Worship Vamana avatar','Offer yellow flowers to Vishnu','Recite Vamana Stotra'],'mantra'=>'ॐ नमो भगवते वासुदेवाय | वामनाय नमः','auspTime'=>'Sunrise — fast broken on Dvadashi'],
        'Krishna_7' => ['name'=>'Indira Ekadashi',   'nameHi'=>'इंदिरा एकादशी',  'significance'=>'Falls in Pitru Paksha. Most powerful for ancestral liberation.','rituals'=>['Observe Ekadashi fast','Perform Pitru Tarpana and Shraddha','Worship Vishnu'],'mantra'=>'ॐ नमो भगवते वासुदेवाय','auspTime'=>'Sunrise — Pitru Tarpana in afternoon'],
        'Shukla_7'  => ['name'=>'Papankusha Ekadashi','nameHi'=>'पापांकुशा एकादशी','significance'=>'Controls sins from multiple lifetimes. Opens gates of heaven.','rituals'=>['Observe Ekadashi fast','Worship Padmanabha Vishnu','Keep night vigil','Recite Vishnu Sahasranama'],'mantra'=>'ॐ नमो भगवते वासुदेवाय | ॐ पद्मनाभाय नमः','auspTime'=>'Sunrise to Dvadashi sunrise'],
        'Krishna_8' => ['name'=>'Rama Ekadashi',     'nameHi'=>'रमा एकादशी',     'significance'=>'Observed before Diwali to please Goddess Lakshmi.','rituals'=>['Observe Ekadashi fast','Worship Lakshmi-Vishnu','Recite Mahalakshmi Stotra','Light lamps for Diwali preparation'],'mantra'=>'ॐ श्रीं महालक्ष्म्यै नमः | ॐ नमो भगवते वासुदेवाय','auspTime'=>'Sunrise — pre-Diwali Lakshmi puja'],
        'Shukla_8'  => ['name'=>'Prabodhini Ekadashi','nameHi'=>'प्रबोधिनी एकादशी','significance'=>'Vishnu awakens — Chaturmasya ends. All auspicious works resume.','rituals'=>['Observe Ekadashi fast','Perform Tulasi Vivaha','Ring bells and blow conch','Light Akhanda Dipa'],'mantra'=>'ॐ नमो भगवते वासुदेवाय | तुलस्यै नमः','auspTime'=>'Evening at dusk — Tulasi Vivaha at Pradosha Kala'],
        'Krishna_9' => ['name'=>'Utpanna Ekadashi',  'nameHi'=>'उत्पन्ना एकादशी', 'significance'=>'Birth of Ekadashi Devi. Observing this gives merit of all Ekadashis.','rituals'=>['Observe Ekadashi fast','Worship Ekadashi Devi','Listen to Utpanna Katha','Keep night vigil'],'mantra'=>'ॐ नमो भगवते वासुदेवाय | ॐ विष्णवे नमः','auspTime'=>'Sunrise to Dvadashi sunrise'],
        'Shukla_9'  => ['name'=>'Mokshada Ekadashi', 'nameHi'=>'मोक्षदा एकादशी — गीता जयंती','significance'=>'Grants liberation. Krishna delivered the Gita on this day — Gita Jayanti.','rituals'=>['Read entire Bhagavad Gita','Observe Ekadashi fast','Donate copies of Bhagavad Gita'],'mantra'=>'ॐ नमो भगवते वासुदेवाय | सर्वधर्मान्परित्यज्य मामेकं शरणं व्रज','auspTime'=>'Sunrise — Gita recitation and Krishna puja'],
        'Krishna_10'=> ['name'=>'Saphala Ekadashi',  'nameHi'=>'सफला एकादशी',   'significance'=>'Giver of success in all endeavors and spheres of life.','rituals'=>['Observe Ekadashi fast','Worship Narayana','Recite Narayana Kavacha'],'mantra'=>'ॐ नमो नारायणाय | ॐ नमो भगवते वासुदेवाय','auspTime'=>'Sunrise to Dvadashi sunrise'],
        'Shukla_10' => ['name'=>'Putrada Ekadashi',  'nameHi'=>'पुत्रदा एकादशी — पौष','significance'=>'Pausha Putrada — grants and protects children.','rituals'=>['Observe Ekadashi fast','Worship Vishnu for children','Chant Santana Gopala mantra'],'mantra'=>'ॐ नमो भगवते वासुदेवाय','auspTime'=>'Sunrise to Dvadashi sunrise'],
        'Krishna_11'=> ['name'=>'Shattila Ekadashi', 'nameHi'=>'षट्तिला एकादशी', 'significance'=>'Six-fold sesame ritual. Purifies karmic debt layer by layer.','rituals'=>['Bathe with sesame water','Apply sesame paste','Perform sesame homa','Donate black sesame','Eat sesame sweets'],'mantra'=>'ॐ नमो भगवते वासुदेवाय','auspTime'=>'Sunrise — sesame bath and donation are mandatory'],
        'Shukla_11' => ['name'=>'Jaya Ekadashi',     'nameHi'=>'जया एकादशी',     'significance'=>'Grants victory and frees from ghostly afflictions.','rituals'=>['Observe Ekadashi fast','Listen to Jaya Katha','Worship Vishnu','Keep night vigil'],'mantra'=>'ॐ नमो भगवते वासुदेवाय','auspTime'=>'Sunrise to Dvadashi sunrise'],
        'Krishna_12'=> ['name'=>'Vijaya Ekadashi',   'nameHi'=>'विजया एकादशी',   'significance'=>'Rama observed this before marching to Lanka. Grants victory in all battles.','rituals'=>['Observe Ekadashi fast','Worship Rama-Vishnu','Read Ramayana','Chant Rama Nama'],'mantra'=>'ॐ रामाय नमः | ॐ नमो भगवते वासुदेवाय','auspTime'=>'Sunrise — pray for victory'],
        'Shukla_12' => ['name'=>'Amalaki Ekadashi',  'nameHi'=>'आमलकी एकादशी',  'significance'=>'Amla tree worship — all deities reside in it. Donating Amla equals donating gold.','rituals'=>['Observe Ekadashi fast','Worship the Amla tree','Eat food under the Amla tree','Donate Amla fruits'],'mantra'=>'ॐ नमो भगवते वासुदेवाय','auspTime'=>'Sunrise — Amla tree worship before breaking fast'],
    ];
}

    // ══════════════════════════════════════════════════════════════
    //  MOON SIGN CHANGE FINDER (Chandra Rashi Pravesh)
    // ══════════════════════════════════════════════════════════════
    private static function findMoonSignChanges(
        int $yr, int $mo, float $lat, float $lon, float $utcOff
    ): array {
        $daysInMonth = (int)(new \DateTime("$yr-$mo-01"))->format('t');
        $changes     = [];
        $prevSign    = -1;

        for ($d = 1; $d <= $daysInMonth; $d++) {
            for ($stepH = 0; $stepH < 24; $stepH += 2) {
                $jd   = self::julianDay($yr, $mo, $d, $stepH - $utcOff);
                $ayan = self::lahiriAyanamsa($jd);
                $moonS= self::n360(self::moonLongitude($jd) - $ayan);
                $sign = (int)floor($moonS / 30.0);

                if ($prevSign !== -1 && $sign !== $prevSign) {
                    // Bisect to 1-minute precision
                    $lo = self::julianDay($yr, $mo, $d, $stepH - 2 - $utcOff);
                    $hi = $jd;
                    for ($i = 0; $i < 40; $i++) {
                        $mid     = ($lo + $hi) / 2.0;
                        $ayanMid = self::lahiriAyanamsa($mid);
                        $ms      = self::n360(self::moonLongitude($mid) - $ayanMid);
                        if ((int)floor($ms / 30.0) === $prevSign) $lo = $mid;
                        else                                        $hi = $mid;
                        if ($hi - $lo < 1.0/1440.0) break;
                    }
                    $jdPravesh = ($lo + $hi) / 2.0;
                    $localHr   = fmod(($jdPravesh + $utcOff/24.0 + 0.5), 1.0) * 24.0;
                    if ($localHr < 0) $localHr += 24.0;
                    $hh = str_pad((int)$localHr, 2, '0', STR_PAD_LEFT);
                    $mm = str_pad((int)(fmod($localHr, 1.0) * 60), 2, '0', STR_PAD_LEFT);
                    // Civil date of pravesh
                    $dateMs = ($jdPravesh - 2440587.5 + $utcOff/24.0) * 86400.0;
                    $dt     = new \DateTime('@' . (int)$dateMs);
                    $changes[] = [
                        'day'     => (int)$dt->format('j'),
                        'sign'    => $sign,
                        'time'    => "$hh:$mm",
                        'localHr' => $localHr,
                    ];
                }
                $prevSign = $sign;
            }
        }
        return $changes;
    }

    // ══════════════════════════════════════════════════════════════
    //  SAMAPTI KAAL — find next boundary crossing via bisection
    // ══════════════════════════════════════════════════════════════
    /**
     * @param float  $jd0     Reference JD (usually sunrise)
     * @param float  $utcOff  UTC offset in hours
     * @param float  $step    Boundary step in degrees (12, 6, or 360/27)
     * @param array  $fnSpec  [$yr,$mo,$dy,$type] — type: 'elong'|'nakSider'|'yogaSum'
     * @param float  $maxDays Search window
     */
    private static function findNextCrossing(
        float $jd0, float $utcOff, float $step, array $fnSpec, float $maxDays
    ): ?array {
        $v0     = self::evalValue($jd0, $fnSpec);
        $curIdx = (int)floor($v0 / $step);
        $target = ($curIdx + 1) * $step;

        // Unwrapped value accumulator
        $unwrap = function(float $jd) use ($jd0, $v0, $fnSpec): float {
            $numSteps = max(2, (int)round(($jd - $jd0) * 24.0));
            $prev  = $v0;
            $accum = 0.0;
            for ($i = 1; $i <= $numSteps; $i++) {
                $jdStep = $jd0 + ($jd - $jd0) * $i / $numSteps;
                $cur    = self::evalValue($jdStep, $fnSpec);
                $diff   = $cur - $prev;
                if ($diff < -180.0) $diff += 360.0;
                if ($diff >  180.0) $diff -= 360.0;
                $accum += $diff;
                $prev   = $cur;
            }
            return $v0 + $accum;
        };

        $hi    = $jd0 + $maxDays;
        $uvHi  = $unwrap($hi);
        if ($uvHi < $target) return null;

        $lo = $jd0;
        for ($i = 0; $i < 60; $i++) {
            $mid = ($lo + $hi) / 2.0;
            if ($unwrap($mid) < $target) $lo = $mid;
            else                         $hi = $mid;
            if ($hi - $lo < 1.0 / (24.0 * 3600.0)) break;
        }

        $jdCross = ($lo + $hi) / 2.0;
        $localHr = fmod($jdCross + $utcOff/24.0 + 0.5, 1.0) * 24.0;
        if ($localHr < 0) $localHr += 24.0;
        return ['jd' => $jdCross, 'localHr' => $localHr];
    }

    /** Evaluate the degree value for Samapti bisection. */
    private static function evalValue(float $jd, array $fnSpec): float
    {
        [,,,, $type] = array_pad($fnSpec, 5, null);
        $ayan = self::lahiriAyanamsa($jd);
        return match ($type) {
            'elong'    => self::n360(self::moonLongitude($jd) - self::sunLongitude($jd)),
            'nakSider' => self::n360(self::moonLongitude($jd) - $ayan),
            'yogaSum'  => self::n360(
                               self::n360(self::moonLongitude($jd) - $ayan) +
                               self::n360(self::sunLongitude($jd)  - $ayan)
                           ),
            default    => 0.0,
        };
    }

    // ══════════════════════════════════════════════════════════════
    //  COMPUTE ONE PLANET
    // ══════════════════════════════════════════════════════════════
    private static function computePlanet(
        float $jd, float $ayan, string $pid, ?bool $forceRetro
    ): array {
        $trop = match ($pid) {
            'sun'     => self::sunLongitude($jd),
            'moon'    => self::moonLongitude($jd),
            'rahu'    => self::rahuLongitude($jd),
            'ketu'    => self::n360(self::rahuLongitude($jd) + 180.0),
            default   => self::planetLongitude($jd, $pid),
        };

        $sider = self::n360($trop - $ayan);
        $ws    = self::$WESTERN_SIGNS[(int)floor($trop / 30.0)];
        $vi    = (int)floor($sider / 30.0);
        $nakSz = 360.0 / 27.0;
        $nak   = self::$NAKSHATRAS[(int)floor($sider / $nakSz)];
        $np    = fmod($sider, $nakSz) / $nakSz;
        $pada  = (int)floor($np * 4.0) + 1;

        $retro = ($forceRetro !== null)
               ? $forceRetro
               : self::isRetrograde($jd, $pid);

        return compact('trop','sider','ws','vi','nak','np','pada','retro');
    }

    // ══════════════════════════════════════════════════════════════
    //  RETROGRADE DETECTION
    // ══════════════════════════════════════════════════════════════
    private static function isRetrograde(float $jd, string $pid): bool
    {
        $calcFn = fn($j) => match ($pid) {
            'sun'  => self::sunLongitude($j),
            'moon' => self::moonLongitude($j),
            default=> self::planetLongitude($j, $pid),
        };
        $d1 = $calcFn($jd - 1.0);
        $d2 = $calcFn($jd + 1.0);
        $diff = $d2 - $d1;
        if ($diff >  180.0) $diff -= 360.0;
        if ($diff < -180.0) $diff += 360.0;
        return $diff < 0.0;
    }

    // ══════════════════════════════════════════════════════════════
    //  KEPLER SOLVER  (Newton–Raphson, converges to 1e-10 radians)
    // ══════════════════════════════════════════════════════════════
    private static function solveKepler(float $M_deg, float $e): float
    {
        $E = self::r($M_deg);
        for ($i = 0; $i < 50; $i++) {
            $dE = (self::r($M_deg) - $E + $e * sin($E)) / (1.0 - $e * cos($E));
            $E += $dE;
            if (abs($dE) < 1e-10) break;
        }
        return $E;
    }

    // ══════════════════════════════════════════════════════════════
    //  UTILITIES
    // ══════════════════════════════════════════════════════════════

    public static function findTithiEnd(float $jdStart, float $utcOff): string
    {
        $res = self::findNextCrossing($jdStart, $utcOff, 12.0, [0,0,0,0,'elong'], 2.0);
        if (!$res) return '—';
        $dt = new \DateTime('@' . (int)round(($res['jd'] - 2440587.5) * 86400));
        $dt->modify(sprintf('%+d minutes', (int)round($utcOff * 60)));
        return $dt->format('Y-m-d H:i:s');
    }

    public static function findNakshatraEnd(float $jdStart, float $utcOff): string
    {
        $res = self::findNextCrossing($jdStart, $utcOff, 360.0/27.0, [0,0,0,0,'nakSider'], 2.0);
        if (!$res) return '—';
        $dt = new \DateTime('@' . (int)round(($res['jd'] - 2440587.5) * 86400));
        $dt->modify(sprintf('%+d minutes', (int)round($utcOff * 60)));
        return $dt->format('Y-m-d H:i:s');
    }

    public static function findYogaEnd(float $jdStart, float $utcOff): string
    {
        $res = self::findNextCrossing($jdStart, $utcOff, 360.0/27.0, [0,0,0,0,'yogaSum'], 2.0);
        if (!$res) return '—';
        $dt = new \DateTime('@' . (int)round(($res['jd'] - 2440587.5) * 86400));
        $dt->modify(sprintf('%+d minutes', (int)round($utcOff * 60)));
        return $dt->format('Y-m-d H:i:s');
    }

    public static function findKaranaEnd(float $jdStart, float $utcOff): string
    {
        $res = self::findNextCrossing($jdStart, $utcOff, 6.0, [0,0,0,0,'elong'], 1.0);
        if (!$res) return '—';
        $dt = new \DateTime('@' . (int)round(($res['jd'] - 2440587.5) * 86400));
        $dt->modify(sprintf('%+d minutes', (int)round($utcOff * 60)));
        return $dt->format('Y-m-d H:i:s');
    }

    public static function calculateSankrantis(int $year, float $utcOff): array
    {
        $sankrantis = [];
        $names = [
            0   => 'Mesh', 30  => 'Vrishabh', 60  => 'Mithun', 90  => 'Kark',
            120 => 'Simha', 150 => 'Kanya', 180 => 'Tula', 210 => 'Vrischik',
            240 => 'Dhanu', 270 => 'Makar', 300 => 'Kumbh', 330 => 'Meen'
        ];

        $jd = self::julianDay($year - 1, 12, 15, 0.0);
        $endJd = self::julianDay($year + 1, 2, 1, 0.0);

        while ($jd < $endJd) {
            $s1 = self::n360(self::sunLongitude($jd) - self::lahiriAyanamsa($jd));
            $s2 = self::n360(self::sunLongitude($jd + 16.0) - self::lahiriAyanamsa($jd + 16.0));
            $idx1 = (int)floor($s1 / 30.0);
            $idx2 = (int)floor($s2 / 30.0);
            
            if ($idx1 !== $idx2) {
                $deg = $idx2 * 30;
                $lo = $jd;
                $hi = $jd + 16.0;
                for ($i = 0; $i < 50; $i++) {
                    $mid = ($lo + $hi) / 2.0;
                    $sMid = self::n360(self::sunLongitude($mid) - self::lahiriAyanamsa($mid));
                    $diff = self::n360($sMid - $deg);
                    if ($diff > 180) {
                        $lo = $mid;
                    } else {
                        $hi = $mid;
                    }
                    if ($hi - $lo < 1.0 / 86400.0) break;
                }
                $jdCross = ($lo + $hi) / 2.0;
                $dt = new \DateTime('@' . (int)round(($jdCross - 2440587.5) * 86400));
                $dt->modify(sprintf('%+d minutes', (int)round($utcOff * 60)));
                
                if ((int)$dt->format('Y') === $year) {
                    $sankrantis[$deg] = [
                        'name' => $names[$deg] . ' Sankranti',
                        'time' => $dt->format('Y-m-d H:i:s'),
                        'jd'   => $jdCross
                    ];
                }
            }
            $jd += 15.0; // Advance carefully
        }
        return $sankrantis;
    }

    /** Normalise to [0, 360) */
    public static function n360(float $x): float
    {
        return fmod(fmod($x, 360.0) + 360.0, 360.0);
    }

    /** Degrees → radians */
    private static function r(float $d): float
    {
        return $d * self::DEG;
    }

    /** Normalise hour to [0, 24) */
    private static function normalHour(float $h): float
    {
        return fmod(fmod($h, 24.0) + 24.0, 24.0);
    }

    /** Format decimal hours as HH:MM:SS */
    public static function decToHMS(float $h): string
    {
        $sign = $h < 0 ? '-' : '';
        $h    = abs($h);
        $hh   = (int)$h;
        $mm   = (int)(($h - $hh) * 60.0);
        $ss   = (int)round((($h - $hh) * 60.0 - $mm) * 60.0);
        return sprintf('%s%02d:%02d:%02d', $sign, $hh, $mm, $ss);
    }

    /** Format degrees as D° M′ S″ */
    public static function dms(float $deg): string
    {
        $d  = (int)floor($deg);
        $ms = ($deg - $d) * 60.0;
        $m  = (int)floor($ms);
        $s  = (int)round(($ms - $m) * 60.0);
        return "{$d}° {$m}′ {$s}″";
    }

    /** Ordinal suffix */
    public static function ordinal(int $n): string
    {
        $s = ['th','st','nd','rd'];
        $v = $n % 100;
        return $n . ($s[($v-20)%10] ?? $s[$v] ?? $s[0]);
    }

    /** Format Samapti local time result */
    private static function fmtSamaptiLocal(?array $result, ?float $riseHr): string
    {
        if ($result === null) return '—';
        $hr = $result['localHr'];
        if ($hr < 0 || $hr > 47) return '—';
        $hh = str_pad((int)($hr % 24), 2, '0', STR_PAD_LEFT);
        $mm = str_pad((int)(fmod($hr, 1.0) * 60.0), 2, '0', STR_PAD_LEFT);
        if ($riseHr !== null && $hr < $riseHr - 0.1) {
            return "▶{$hh}:{$mm}";   // ends before next sunrise → next day
        }
        return "{$hh}:{$mm}";
    }
}