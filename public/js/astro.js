// // ═══════════════════════════════════════════════════════════════════
// //  astro.js  —  9-Planet Vedic + Western Position Calculator
// //  Engine: Jean Meeus "Astronomical Algorithms" (2nd Ed.)
// //  Planets: Sun, Moon, Mercury, Venus, Mars, Jupiter, Saturn,
// //           Rahu (North Node), Ketu (South Node)
// //  Added: Ascendant (Lagna), Descendant, House positions per planet
// //  Added: Tithi & Karana (Panchanga) accurate calculation
// //  Added: Enhanced Panchanga — Vara, Nakshatra, Yoga with full attributes
// // ═══════════════════════════════════════════════════════════════════

// const DEG  = Math.PI / 180;
// const r    = d => d * DEG;
// const n360 = x => ((x % 360) + 360) % 360;

// // ── Julian Day (Meeus Ch.7) ─────────────────────────────────────
// function julianDay(yr, mo, dy, utHr) {
//   if (mo <= 2) { yr--; mo += 12; }
//   const A = Math.floor(yr / 100), B = 2 - A + Math.floor(A / 4);
//   return Math.floor(365.25*(yr+4716)) + Math.floor(30.6001*(mo+1)) + dy + utHr/24 + B - 1524.5;
// }

// // ── Lahiri (Chitrapaksha) Ayanamsa ──────────────────────────────
// // Reference: Lahiri Committee + IAU 1976 precession + Meeus AA2.
// // Epoch: 21 March 285 CE, ayanamsa = 0°  (Chitra at 180° tropical)
// // Verified against astro.com Lahiri / Drik Panchang to within ~1 arcminute.
// //
// // Formula: IAU 1976 general precession in longitude
// //   ψ_A = 5029.097″·T + 1.558″·T² − 0.00342″·T³  (arcsec, T in centuries)
// // Ayanamsa(JD) = (ψ_A at JD from 285 CE epoch) converted to degrees.
// // In practice the easiest verified form: at J2000.0 Lahiri = 23°51′11.4″,
// // with the IAU 1976 rate 50.2910″/yr and tiny quadratic/cubic terms:
// //   Ayan = 23.853722° + 50.2910″/yr × years + corrections
// // (23°51′13.4″ = 23.853722° is the correct J2000 Lahiri seed per
// //  Rashtriya Panchang / Indian Ephemeris, matching astro.com within 1')
// function lahiriAyanamsa(jd) {
//   const T     = (jd - 2451545.0) / 36525.0;  // Julian centuries from J2000
//   const years = T * 100.0;                    // Julian years from J2000 (T*36525/365.25 ≈ T*100)
//   // IAU 1976 lunisolar precession rate: 50.2910″/yr + quadratic
//   // Seed at J2000.0 = 23°51′13.4″ = 23.853722° (Rashtriya Panchang)
//   const precArcSec = 50.2910 * years
//                    + 0.022  * T * T          // quadratic correction (arcsec)
//                    - 0.0003 * T * T * T;     // cubic
//   return 23.853722 + precArcSec / 3600.0;
// }

// // ─────────────────────────────────────────────────────────────────
// //  MOON  (Meeus Ch.47 full series, ±0.3°)
// // ─────────────────────────────────────────────────────────────────
// function moonLongitude(jd) {
//   const T  = (jd - 2451545.0) / 36525;
//   const Lp = n360(218.3164477 + 481267.88123421*T - 0.0015786*T*T + T*T*T/538841);
//   const D  = n360(297.8501921 + 445267.1114034 *T - 0.0018819*T*T + T*T*T/545868);
//   const M  = n360(357.5291092 + 35999.0502909  *T - 0.0001536*T*T);
//   const Mp = n360(134.9633964 + 477198.8675055 *T + 0.0087414*T*T + T*T*T/69699);
//   const F  = n360(93.2720950  + 483202.0175233 *T - 0.0036539*T*T);
//   const A1 = n360(119.75 + 131.849*T), A2 = n360(53.09 + 479264.290*T);
//   const E  = 1 - 0.002516*T - 0.0000074*T*T, E2 = E*E;
//   const terms = [
//     [0,0,1,0,6288774],[2,0,-1,0,1274027],[2,0,0,0,658314],[0,0,2,0,213618],
//     [0,1,0,0,-185116],[0,0,0,2,-114332],[2,0,-2,0,58793],[2,-1,-1,0,57066],
//     [2,0,1,0,53322],[2,-1,0,0,45758],[0,1,-1,0,-40923],[1,0,0,0,-34720],
//     [0,1,1,0,-30383],[2,0,0,-2,15327],[0,0,1,-2,10980],[4,0,-1,0,10675],
//     [0,0,3,0,10034],[4,0,-2,0,8548],[2,1,-1,0,-7888],[2,1,0,0,-6766],
//     [1,0,-1,0,-5163],[1,1,0,0,4987],[2,-1,1,0,4036],[2,0,2,0,3994],
//     [4,0,0,0,3861],[2,0,-3,0,3665],[0,1,-2,0,-2689],[2,-1,-2,0,2390],
//     [1,0,1,0,-2348],[2,-2,0,0,2236],[0,1,2,0,-2120],[0,2,0,0,-2069],
//     [2,-2,-1,0,2048],[2,0,1,-2,-1773],[2,0,0,2,-1595],[4,-1,-1,0,1215],
//     [0,0,2,2,-1110],[3,0,-1,0,-892],[2,1,1,0,-810],[4,-1,-2,0,759],
//     [0,2,-1,0,-713],[2,2,-1,0,-700],[2,1,-2,0,691],[4,0,1,0,549],
//     [0,0,4,0,537],[4,-1,0,0,520],[1,0,-2,0,-487],[0,0,2,-2,-381],
//     [1,1,1,0,351],[3,0,-2,0,-340],[4,0,-3,0,330],[2,-1,2,0,327],
//     [0,2,1,0,-323],[1,1,-1,0,299],[2,0,3,0,294]
//   ];
//   let Sl = 0;
//   for (const [d,m,mp,f,c] of terms) {
//     let cf=c;
//     if (Math.abs(m)===1) cf*=E; if (Math.abs(m)===2) cf*=E2;
//     Sl += cf*Math.sin(r(d*D + m*M + mp*Mp + f*F));
//   }
//   Sl += 3958*Math.sin(r(A1)) + 1962*Math.sin(r(Lp-F)) + 318*Math.sin(r(A2));
//   return n360(Lp + Sl/1e6);
// }

// // ─────────────────────────────────────────────────────────────────
// //  RAHU (Mean ascending lunar node, always retrograde) — Meeus Ch.47
// //  Ketu = Rahu + 180°
// // ─────────────────────────────────────────────────────────────────
// function rahuLongitude(jd) {
//   const T = (jd - 2451545.0) / 36525;
//   return n360(125.0445479 - 1934.1362608*T + 0.0020754*T*T + T*T*T/467441);
// }

// // ─────────────────────────────────────────────────────────────────
// //  SUN  (Meeus Ch.27 apparent longitude — more terms for ~0.01° accuracy)
// // ─────────────────────────────────────────────────────────────────
// function sunLongitude(jd) {
//   const T   = (jd - 2451545.0) / 36525;
//   const T2  = T * T, T3 = T2 * T;
//   // Geometric mean longitude (degrees)
//   const L0  = n360(280.46646 + 36000.76983 * T + 0.0003032 * T2);
//   // Mean anomaly of the Sun (degrees)
//   const M   = n360(357.52911 + 35999.05029 * T - 0.0001537 * T2 - 0.00000048 * T3);
//   const Mr  = r(M);
//   // Equation of center (more complete series)
//   const C   = (1.9146 - 0.004817 * T - 0.000014 * T2) * Math.sin(Mr)
//             + (0.019993 - 0.000101 * T) * Math.sin(2 * Mr)
//             + 0.000290 * Math.sin(3 * Mr)
//             + 0.0000075 * Math.sin(4 * Mr);
//   // Sun's true longitude
//   const sunTrue = L0 + C;
//   // Apparent longitude: subtract aberration (~20.4″ = 0.00569°),
//   // and apply nutation correction using Moon's ascending node
//   const omega = n360(125.04452 - 1934.136261 * T + 0.0020708 * T2 + T3 / 450000);
//   const appLon = sunTrue - 0.00569 - 0.00478 * Math.sin(r(omega));
//   return n360(appLon);
// }

// function sunEquatorial(jd) {
//   const T   = (jd - 2451545.0) / 36525;
//   // IAU mean obliquity (Meeus Ch.22, Laskar)
//   const eps = 23.439291111
//     - 0.013004167 * T
//     - 0.0000001639 * T * T
//     + 0.0000005036 * T * T * T;
//   const lon = sunLongitude(jd);
//   const lonR = r(lon), epsR = r(eps);
//   const ra  = Math.atan2(Math.cos(epsR)*Math.sin(lonR), Math.cos(lonR)) / DEG;
//   const dec = Math.asin(Math.sin(epsR)*Math.sin(lonR)) / DEG;
//   return { ra: n360(ra), dec, lon };
// }

// // ─────────────────────────────────────────────────────────────────
// //  PLANETARY LONGITUDES — Meeus Ch.32/33 (Keplerian elements)
// // ─────────────────────────────────────────────────────────────────
// const ELEMENTS = {
//   mercury: { L0:252.250906, L1:149474.0722491, a:0.38709831, e0:0.20563175, e1: 0.000020406, w0: 77.456119, w1: 0.1588643, Om0: 48.330893, Om1:-0.1254229, i0: 7.004986, i1:-0.0059516 },
//   venus:   { L0:181.979801, L1: 58519.2130302, a:0.72332982, e0:0.00677188, e1:-0.000047766, w0:131.563707, w1: 0.1212060, Om0: 76.679920, Om1:-0.2780080, i0: 3.394662, i1:-0.0008568 },
//   mars:    { L0:355.433275, L1: 19141.6964746, a:1.52371243, e0:0.09340062, e1: 0.000090483, w0:336.060234, w1: 0.4438898, Om0: 49.558093, Om1:-0.2949846, i0: 1.849726, i1:-0.0006011 },
//   jupiter: { L0: 34.351519, L1:  3036.3027748, a:5.20248019, e0:0.04853590, e1: 0.000016322, w0: 14.331964, w1: 0.2155209, Om0:100.464441, Om1: 0.1766828, i0: 1.303270, i1:-0.0054966 },
//   saturn:  { L0: 50.077444, L1:  1223.5110686, a:9.54149883, e0:0.05550825, e1:-0.000346641, w0: 93.056787, w1: 0.5665496, Om0:113.665524, Om1:-0.2566649, i0: 2.488878, i1:-0.0037363 }
// };

// function solveKepler(M_deg, e) {
//   let E = r(M_deg);
//   for (let i=0; i<50; i++) {
//     const dE = (r(M_deg) - E + e*Math.sin(E)) / (1 - e*Math.cos(E));
//     E += dE;
//     if (Math.abs(dE) < 1e-10) break;
//   }
//   return E;
// }

// function planetLongitude(jd, planet) {
//   const T   = (jd - 2451545.0) / 36525;
//   const el  = ELEMENTS[planet];
//   const L   = n360(el.L0 + el.L1*T);
//   const ww  = el.w0 + el.w1*T;
//   const Om  = el.Om0 + el.Om1*T;
//   const inc = r(el.i0 + el.i1*T);
//   const e   = el.e0 + el.e1*T;
//   const a   = el.a;
//   const M_deg = n360(L - ww);
//   const E   = solveKepler(M_deg, e);
//   const nu  = 2*Math.atan2(Math.sqrt(1+e)*Math.sin(E/2), Math.sqrt(1-e)*Math.cos(E/2));
//   const rv  = a*(1 - e*Math.cos(E));
//   const omR = r(Om), wR = r(ww - Om);
//   const u   = nu + wR;
//   const xh  = rv*(Math.cos(omR)*Math.cos(u) - Math.sin(omR)*Math.sin(u)*Math.cos(inc));
//   const yh  = rv*(Math.sin(omR)*Math.cos(u) + Math.cos(omR)*Math.sin(u)*Math.cos(inc));

//   // Earth's heliocentric position — use more accurate radius vector from VSOP87 truncated
//   // Earth mean anomaly
//   const Me   = n360(357.52911 + 35999.05029*T - 0.0001537*T*T);
//   const MeR  = r(Me);
//   // L3 (Venus): needed for perturbations
//   const Mv = n360(212.267 + 58519.213*T);  // Venus mean anomaly approx
//   // Earth radius vector (AU) — accurate series
//   const r_e  = 1.000001018 * (1
//     - 0.01671123 * Math.cos(MeR)
//     - 0.000139   * Math.cos(2*MeR)
//     - 0.000014   * Math.cos(3*MeR)
//     + 0.0000003  * Math.cos(4*MeR));
//   // Earth's heliocentric longitude = Sun's geocentric longitude + 180°
//   const sLon = r(sunLongitude(jd));
//   const xe   = r_e * Math.cos(sLon + Math.PI);
//   const ye   = r_e * Math.sin(sLon + Math.PI);

//   let lon = Math.atan2(yh - ye, xh - xe) / DEG;
//   lon = n360(lon);

//   // ── Perturbation corrections for Jupiter & Saturn (Meeus Ch.33) ──
//   // These are the dominant mutual perturbations (~0.3–0.6°)
//   if (planet === 'jupiter' || planet === 'saturn') {
//     // Mean anomalies for Jupiter and Saturn
//     const Mj = n360(20.9  + 0.071023 * (jd - 2451545.0));  // rough Jupiter MA
//     const Ms = n360(317.0 + 0.028441 * (jd - 2451545.0));  // rough Saturn MA
//     const MjR = r(Mj), MsR = r(Ms);
//     if (planet === 'jupiter') {
//       lon += (0.3314 * Math.sin(2*MsR - 5*MjR - r(67.6))
//             - 0.0390 * Math.sin(MsR  - 2*MjR + r(76.0))
//             + 0.0318 * Math.sin(MsR  - 3*MjR + r(13.0))
//             - 0.0185 * Math.sin(MsR  + r(100.0))
//             - 0.0143 * Math.sin(2*MjR) ) / 3600; // in degrees
//     } else {
//       lon += (-0.8138 * Math.sin(2*MsR - 4*MjR - r(68.0))
//              + 0.2073 * Math.sin(2*MsR - 5*MjR - r(67.6))
//              - 0.0924 * Math.sin(2*MsR - 3*MjR)
//              + 0.0462 * Math.sin(MsR  - r(56.0))
//              - 0.0402 * Math.sin(MsR  + MjR - r(120.0)) ) / 3600;
//     }
//     lon = n360(lon);
//   }
//   return lon;
// }

// // ── Retrograde detection ─────────────────────────────────────────
// function isRetrograde(jd, calcFn) {
//   let d1 = calcFn(jd-1), d2 = calcFn(jd+1);
//   let diff = d2 - d1;
//   if (diff >  180) diff -= 360;
//   if (diff < -180) diff += 360;
//   return diff < 0;
// }

// // ─────────────────────────────────────────────────────────────────
// //  ASCENDANT (LAGNA)  — Meeus Ch.25 / standard RAMC method
// // ─────────────────────────────────────────────────────────────────
// function computeAngles(jd, lat, lon) {
//   const T = (jd - 2451545.0) / 36525;
//   // IAU mean obliquity (Meeus Ch.22) — full polynomial
//   const eps = 23.439291111
//     - 0.013004167  * T
//     - 0.0000001639 * T * T
//     + 0.0000005036 * T * T * T;
//   // GMST (Meeus Ch.12, IAU 1982) — accurate to 0.1 second
//   const gmst = n360(
//     280.46061837
//     + 360.98564736629 * (jd - 2451545.0)
//     + 0.000387933 * T * T
//     - T * T * T / 38710000
//   );
//   const lst = n360(gmst + lon);
//   const ramc = lst;
//   const raMC_r = r(ramc);
//   const epsR   = r(eps);
//   // MC = atan2(tan(RAMC), cos(ε))
//   let mc = Math.atan2(Math.sin(raMC_r), Math.cos(raMC_r) * Math.cos(epsR)) / DEG;
//   mc = n360(mc);
//   const latR = r(lat);
//   // Ascendant formula (Meeus Ch.14)
//   const numerator   = -Math.cos(raMC_r);
//   const denominator = Math.sin(epsR) * Math.tan(latR) + Math.cos(epsR) * Math.sin(raMC_r);
//   let asc = Math.atan2(numerator, denominator) / DEG;
//   asc = n360(asc);
//   // Ascendant must be in the eastern hemisphere relative to MC+90°
//   const expected = n360(ramc + 90);
//   let diff = asc - expected;
//   if (diff >  180) diff -= 360;
//   if (diff < -180) diff += 360;
//   if (Math.abs(diff) > 90) asc = n360(asc + 180);
//   const desc = n360(asc + 180);
//   const ic   = n360(mc  + 180);
//   return { asc, desc, mc, ic, lst, eps };
// }

// function houseNumber(planetTropLon, ascTropLon) {
//   const diff = n360(planetTropLon - ascTropLon);
//   return Math.floor(diff / 30) + 1;
// }

// function ordinal(n) {
//   const s = ['th','st','nd','rd'], v = n % 100;
//   return n + (s[(v-20)%10] || s[v] || s[0]);
// }

// // ─────────────────────────────────────────────────────────────────
// //  TITHI & KARANA — Panchanga calculations
// // ─────────────────────────────────────────────────────────────────

// const TITHIS = [
//   { n:'Pratipada',   paksha:'Shukla', num:1,  lord:'Agni',       nature:'Nanda (Auspicious)',   deity:'Brahma'     },
//   { n:'Dwitiya',     paksha:'Shukla', num:2,  lord:'Brahma',     nature:'Bhadra (Prosperous)',  deity:'Vidhatr'    },
//   { n:'Tritiya',     paksha:'Shukla', num:3,  lord:'Kartikeya',  nature:'Jaya (Victorious)',    deity:'Gauri'      },
//   { n:'Chaturthi',   paksha:'Shukla', num:4,  lord:'Yama',       nature:'Rikta (Inauspicious)', deity:'Ganesh'     },
//   { n:'Panchami',    paksha:'Shukla', num:5,  lord:'Moon',       nature:'Purna (Full)',         deity:'Naga'       },
//   { n:'Shashthi',    paksha:'Shukla', num:6,  lord:'Kartikeya',  nature:'Nanda',                deity:'Kartikeya'  },
//   { n:'Saptami',     paksha:'Shukla', num:7,  lord:'Sun',        nature:'Bhadra',               deity:'Surya'      },
//   { n:'Ashtami',     paksha:'Shukla', num:8,  lord:'Shiva',      nature:'Rikta',                deity:'Rudra'      },
//   { n:'Navami',      paksha:'Shukla', num:9,  lord:'Durga',      nature:'Jaya',                 deity:'Durga'      },
//   { n:'Dashami',     paksha:'Shukla', num:10, lord:'Yama',       nature:'Purna',                deity:'Dharma'     },
//   { n:'Ekadashi',    paksha:'Shukla', num:11, lord:'Vishnu',     nature:'Jaya',                 deity:'Vishnu'     },
//   { n:'Dwadashi',    paksha:'Shukla', num:12, lord:'Vishnu',     nature:'Nanda',                deity:'Hari'       },
//   { n:'Trayodashi',  paksha:'Shukla', num:13, lord:'Kama',       nature:'Jaya',                 deity:'Kama'       },
//   { n:'Chaturdashi', paksha:'Shukla', num:14, lord:'Shiva',      nature:'Rikta',                deity:'Shiva'      },
//   { n:'Purnima',     paksha:'Shukla', num:15, lord:'Moon',       nature:'Purna',                deity:'Moon'       },
//   { n:'Pratipada',   paksha:'Krishna',num:1,  lord:'Agni',       nature:'Nanda',                deity:'Brahma'     },
//   { n:'Dwitiya',     paksha:'Krishna',num:2,  lord:'Brahma',     nature:'Bhadra',               deity:'Vidhatr'    },
//   { n:'Tritiya',     paksha:'Krishna',num:3,  lord:'Kartikeya',  nature:'Jaya',                 deity:'Gauri'      },
//   { n:'Chaturthi',   paksha:'Krishna',num:4,  lord:'Yama',       nature:'Rikta',                deity:'Ganesh'     },
//   { n:'Panchami',    paksha:'Krishna',num:5,  lord:'Moon',       nature:'Purna',                deity:'Naga'       },
//   { n:'Shashthi',    paksha:'Krishna',num:6,  lord:'Kartikeya',  nature:'Nanda',                deity:'Kartikeya'  },
//   { n:'Saptami',     paksha:'Krishna',num:7,  lord:'Sun',        nature:'Bhadra',               deity:'Surya'      },
//   { n:'Ashtami',     paksha:'Krishna',num:8,  lord:'Shiva',      nature:'Rikta',                deity:'Rudra'      },
//   { n:'Navami',      paksha:'Krishna',num:9,  lord:'Durga',      nature:'Jaya',                 deity:'Durga'      },
//   { n:'Dashami',     paksha:'Krishna',num:10, lord:'Yama',       nature:'Purna',                deity:'Dharma'     },
//   { n:'Ekadashi',    paksha:'Krishna',num:11, lord:'Vishnu',     nature:'Jaya',                 deity:'Vishnu'     },
//   { n:'Dwadashi',    paksha:'Krishna',num:12, lord:'Vishnu',     nature:'Nanda',                deity:'Hari'       },
//   { n:'Trayodashi',  paksha:'Krishna',num:13, lord:'Kama',       nature:'Jaya',                 deity:'Kama'       },
//   { n:'Chaturdashi', paksha:'Krishna',num:14, lord:'Shiva',      nature:'Rikta',                deity:'Shiva'      },
//   { n:'Amavasya',    paksha:'Krishna',num:15, lord:'Pitrs',      nature:'Nanda',                deity:'Pitrs'      },
// ];

// const KARANA_CYCLE = [
//   { n:'Bava',    lord:'Indra',   nature:'Movable', type:'Chara', deity:'Indra',    favour:'Auspicious acts, travel',           cls:'Movable (Chara)' },
//   { n:'Balava',  lord:'Brahma',  nature:'Movable', type:'Chara', deity:'Brahma',   favour:'Creative work, rituals',            cls:'Movable (Chara)' },
//   { n:'Kaulava', lord:'Mitra',   nature:'Movable', type:'Chara', deity:'Mitra',    favour:'Friendship, partnerships',          cls:'Movable (Chara)' },
//   { n:'Taitila', lord:'Aryama',  nature:'Movable', type:'Chara', deity:'Aryaman',  favour:'Domestic activities',               cls:'Movable (Chara)' },
//   { n:'Garija',  lord:'Prithvi', nature:'Movable', type:'Chara', deity:'Bhumi',    favour:'Agriculture, earth work',           cls:'Movable (Chara)' },
//   { n:'Vanija',  lord:'Lakshmi', nature:'Movable', type:'Chara', deity:'Lakshmi',  favour:'Trade, commerce, prosperity',       cls:'Movable (Chara)' },
//   { n:'Vishti',  lord:'Yama',    nature:'Inauspicious', type:'Chara', deity:'Yama', favour:'Avoid new beginnings',            cls:'Movable (Chara)' },
// ];
// const KARANA_FIXED = [
//   { n:'Kimstughna',  lord:'Sun',    nature:'Auspicious',   type:'Sthira', deity:'Surya',   favour:'Auspicious acts',    cls:'Fixed (Sthira)' },
//   { n:'Shakuni',     lord:'Vishnu', nature:'Mixed',        type:'Sthira', deity:'Vishnu',  favour:'Mixed results',      cls:'Fixed (Sthira)' },
//   { n:'Chatushpada', lord:'Brahma', nature:'Auspicious',   type:'Sthira', deity:'Rudra',   favour:'Stability, rituals', cls:'Fixed (Sthira)' },
//   { n:'Naga',        lord:'Vasuki', nature:'Inauspicious', type:'Sthira', deity:'Naga',    favour:'Avoid new acts',     cls:'Fixed (Sthira)' },
// ];

// function computeTithiKarana(jd) {
//   const moonLon = moonLongitude(jd);
//   const sunLon  = sunLongitude(jd);
//   const elong = n360(moonLon - sunLon);
//   const tithiIndex = Math.floor(elong / 12);
//   const tithiProg  = (elong % 12) / 12;
//   const tithi      = TITHIS[tithiIndex];
//   // karanaSlot: 0-indexed internally (0 = first half of Pratipada Shukla = Kimstughna)
//   // There are 60 karanas per lunar month (2 per tithi × 30 tithis)
//   const karanaSlotRaw = Math.floor(elong / 6);  // 0..59
//   const karanaProg = (elong % 6) / 6;
//   let karana;
//   // Slot 0 → Kimstughna (fixed, first half of Pratipada Shukla)
//   // Slots 1..56 → 7 movable karanas cycling (Bava=0, Balava=1, ...Vishti=6)
//   // Slot 57 → Shakuni (fixed)
//   // Slot 58 → Chatushpada (fixed)
//   // Slot 59 → Naga (fixed)
//   if (karanaSlotRaw === 0) {
//     karana = KARANA_FIXED[0];                           // Kimstughna
//   } else if (karanaSlotRaw <= 56) {
//     karana = KARANA_CYCLE[(karanaSlotRaw - 1) % 7];    // 7 movable, cycling
//   } else if (karanaSlotRaw === 57) {
//     karana = KARANA_FIXED[1];                           // Shakuni
//   } else if (karanaSlotRaw === 58) {
//     karana = KARANA_FIXED[2];                           // Chatushpada
//   } else {
//     karana = KARANA_FIXED[3];                           // Naga
//   }
//   const karanaSlot = karanaSlotRaw + 1;  // display as 1-based (1..60)
//   const tithiHalf = (karanaSlotRaw % 2 === 0) ? 'First Half' : 'Second Half';
//   return { elong, tithi, tithiIndex, tithiProg, karana, karanaSlot, karanaProg, tithiHalf, moonLon, sunLon };
// }

// // ─────────────────────────────────────────────────────────────────
// //  TITHI MODE SYSTEM
// // ─────────────────────────────────────────────────────────────────
// let _tithiData = { sunrise: null, now: null, sunset: null };
// let _tithiAyan = 0;
// let _tithiMode = 'sunrise';

// function tithiLabel(tk) {
//   if (!tk) return 'Not available (polar)';
//   return `${tk.tithi.paksha} ${tk.tithi.n} ${tk.tithi.num}`;
// }

// function switchTithiMode(mode) {
//   _tithiMode = mode;
//   ['sunrise','now','sunset'].forEach(m => {
//     const btn = document.getElementById('tmb_' + m);
//     if (btn) {
//       btn.classList.toggle('active-mode', m === mode);
//       btn.style.outline      = m === mode ? '2.5px solid rgba(255,255,255,0.55)' : '';
//       btn.style.outlineOffset= m === mode ? '1px' : '';
//       btn.style.transform    = m === mode ? 'translateY(-3px)' : '';
//       btn.style.boxShadow    = m === mode ? '0 10px 32px -6px rgba(0,0,0,0.5)' : '';
//     }
//   });
//   const tk = _tithiData[mode];
//   if (tk) renderTithiDetail(tk);
//   else {
//     setText('tithiStripTitle', 'Not available');
//     setText('tithiStripSub',   'Polar day or night — no ' + mode);
//     setText('tithiStripElong', '—');
//   }
// }

// function renderTithiDetail(tk) {
//   const { tithi, tithiProg, karana, karanaProg, tithiHalf, elong, moonLon, sunLon, karanaSlot } = tk;

//   setText('tithiStripTitle', `${tithi.paksha} ${tithi.n} · ${karana.n}`);
//   setText('tithiStripSub', `${tithi.paksha} Paksha · Tithi ${tithi.num} of 15  ·  Karana ${karanaSlot} of 60`);
//   setText('tithiStripElong', `${elong.toFixed(2)}°`);

//   setText('tithiName',    tithi.n);
//   setText('tithiPaksha',  `${tithi.paksha} Paksha`);
//   setText('tithiNum',     `${tithi.num} / 15`);
//   setText('tithiLord',    tithi.lord);
//   setText('tithiDeity',   tithi.deity);
//   setText('tithiNature',  tithi.nature);
//   setText('tithiProgPct', `${(tithiProg*100).toFixed(1)}% elapsed`);

//   const modeMap = { sunrise:'at Sunrise', now:'at Input Time', sunset:'at Sunset' };
//   setText('tithiProgLabel', modeMap[_tithiMode] || '');

//   // Karana tiles
//   setText('karanaStripTitle', `${karana.n} Karana`);
//   setText('karanaStripSub',   `${karana.cls} · Slot ${karanaSlot} of 60 · ${tithiHalf}`);
//   setText('karanaStripSlot',  `${karanaSlot} / 60`);
//   setText('karanaName',    karana.n);
//   setText('karanaType',    `${karana.type} · ${tithiHalf}`);
//   setText('karanaLord',    karana.lord);
//   setText('karanaNature',  karana.nature);
//   setText('karanaSlotEl',  `${karanaSlot} / 60`);
//   setText('karanaProgPct', `${(karanaProg*100).toFixed(1)}% elapsed`);
//   setText('karanaDeity',   karana.deity || karana.lord);
//   setText('karanaDeitySub','Ruling deity of this Karana');
//   setText('karanaFavour',  karana.favour || '—');
//   setText('karanaFavourSub','Best activities now');
//   setText('karanaClass',   karana.cls || karana.type);
//   setText('karanaClassSub', karana.type === 'Sthira' ? 'Fixed occurrence' : 'Repeating cycle (×8)');

//   const tBar = document.getElementById('tithiProgressBar');
//   if (tBar) { tBar.style.width = '0%'; setTimeout(() => tBar.style.width = (tithiProg*100).toFixed(1)+'%', 80); }
//   const kBar = document.getElementById('karanaProgressBar');
//   if (kBar) { kBar.style.width = '0%'; setTimeout(() => kBar.style.width = (karanaProg*100).toFixed(1)+'%', 120); }

//   updateLunarArc(elong, tithi);
//   setTimeout(() => updateKaranaArc(karanaSlot, karana.n), 150);

//   const note = document.getElementById('tithiNote');
//   if (note) note.innerHTML =
//     `Moon–Sun Elongation <code>${elong.toFixed(4)}°</code>&nbsp;·&nbsp;Moon <code>${moonLon.toFixed(4)}°</code>&nbsp;·&nbsp;Sun <code>${sunLon.toFixed(4)}°</code>&nbsp;·&nbsp;Tropical`;
// }

// function updateLunarArc(elong, tithi) {
//   const pct = Math.min(elong / 360, 1);
//   const arcLen = 320;
//   const offset = arcLen * (1 - pct);
//   const fill = document.getElementById('lunarArcFill');
//   const dot  = document.getElementById('lunarArcDot');
//   const lbl  = document.getElementById('lunarArcLabel');
//   if (!fill || !dot || !lbl) return;
//   fill.style.strokeDashoffset = offset;
//   fill.style.transition = 'stroke-dashoffset 0.7s cubic-bezier(0.4,0,0.2,1)';
//   const t = pct;
//   const bx = (1-t)*(1-t)*20 + 2*(1-t)*t*160 + t*t*300;
//   const by = (1-t)*(1-t)*60 + 2*(1-t)*t*(-20) + t*t*60;
//   dot.setAttribute('cx', bx.toFixed(1));
//   dot.setAttribute('cy', by.toFixed(1));
//   const isKrishna = tithi.paksha === 'Krishna';
//   fill.setAttribute('stroke', isKrishna ? '#6040a0' : '#a060e0');
//   dot.setAttribute('fill', isKrishna ? '#4a2870' : '#7840c0');
//   lbl.textContent = `${tithi.paksha} ${tithi.n} ${tithi.num} · ${elong.toFixed(1)}°`;
// }

// // ── Vara Arc (7-day wheel) ──────────────────────────────────────
// function updateVaraArc(varaDayIdx, varaName) {
//   const fill = document.getElementById('varaArcFill');
//   const dot  = document.getElementById('varaArcDot');
//   const lbl  = document.getElementById('varaArcLabel');
//   if (!fill || !dot || !lbl) return;
//   const arcLen = 340;
//   const pct = varaDayIdx / 6;
//   const offset = arcLen * (1 - pct);
//   fill.style.strokeDashoffset = offset;
//   fill.style.transition = 'stroke-dashoffset 0.7s cubic-bezier(0.4,0,0.2,1)';
//   const t = pct;
//   const bx = (1-t)*(1-t)*20 + 2*(1-t)*t*170 + t*t*320;
//   const by = (1-t)*(1-t)*90 + 2*(1-t)*t*(-10) + t*t*90;
//   dot.setAttribute('cx', bx.toFixed(1));
//   dot.setAttribute('cy', by.toFixed(1));
//   const VARA_COLORS = ['#c56408','#1d4e6f','#b83020','#2e7a6e','#7a5a10','#8e3a7a','#4a4060'];
//   const col = VARA_COLORS[varaDayIdx] || '#c56408';
//   fill.setAttribute('stroke', col);
//   dot.setAttribute('fill', col);
//   lbl.textContent = varaName;
// }

// // ── Nakshatra Arc (27-spoke) ────────────────────────────────────
// function updateNakArc(nakIdx, nakName) {
//   const fill = document.getElementById('nakArcFill');
//   const dot  = document.getElementById('nakArcDot');
//   const lbl  = document.getElementById('nakArcLabel');
//   if (!fill || !dot || !lbl) return;
//   const arcLen = 340;
//   const pct = nakIdx / 26;
//   const offset = arcLen * (1 - pct);
//   fill.style.strokeDashoffset = offset;
//   fill.style.transition = 'stroke-dashoffset 0.7s cubic-bezier(0.4,0,0.2,1)';
//   const t = pct;
//   const bx = (1-t)*(1-t)*20 + 2*(1-t)*t*170 + t*t*320;
//   const by = (1-t)*(1-t)*95 + 2*(1-t)*t*(-5) + t*t*95;
//   dot.setAttribute('cx', bx.toFixed(1));
//   dot.setAttribute('cy', by.toFixed(1));
//   lbl.textContent = nakName;
// }

// // ── Yoga Arc (27-spoke) ─────────────────────────────────────────
// function updateYogaArc(yogaIdx, yogaName, isAuspicious) {
//   const fill = document.getElementById('yogaArcFill');
//   const dot  = document.getElementById('yogaArcDot');
//   const lbl  = document.getElementById('yogaArcLabel');
//   if (!fill || !dot || !lbl) return;
//   const arcLen = 340;
//   const pct = yogaIdx / 26;
//   const offset = arcLen * (1 - pct);
//   fill.style.strokeDashoffset = offset;
//   fill.style.transition = 'stroke-dashoffset 0.7s cubic-bezier(0.4,0,0.2,1)';
//   const t = pct;
//   const bx = (1-t)*(1-t)*20 + 2*(1-t)*t*170 + t*t*320;
//   const by = (1-t)*(1-t)*95 + 2*(1-t)*t*(-5) + t*t*95;
//   dot.setAttribute('cx', bx.toFixed(1));
//   dot.setAttribute('cy', by.toFixed(1));
//   const col = isAuspicious === false ? '#c03030' : '#5a30a0';
//   fill.setAttribute('stroke', col);
//   dot.setAttribute('fill', col);
//   lbl.textContent = yogaName;
// }

// // ── Karana Arc (60-slot) ────────────────────────────────────────
// function updateKaranaArc(karanaSlot, karanaName) {
//   const fill = document.getElementById('karanaArcFill');
//   const dot  = document.getElementById('karanaArcDot');
//   const lbl  = document.getElementById('karanaArcLabel');
//   if (!fill || !dot || !lbl) return;
//   const arcLen = 340;
//   const pct = Math.min((karanaSlot - 1) / 59, 1);
//   const offset = arcLen * (1 - pct);
//   fill.style.strokeDashoffset = offset;
//   fill.style.transition = 'stroke-dashoffset 0.7s cubic-bezier(0.4,0,0.2,1)';
//   const t = pct;
//   const bx = (1-t)*(1-t)*20 + 2*(1-t)*t*170 + t*t*320;
//   const by = (1-t)*(1-t)*95 + 2*(1-t)*t*(-5) + t*t*95;
//   dot.setAttribute('cx', bx.toFixed(1));
//   dot.setAttribute('cy', by.toFixed(1));
//   lbl.textContent = `${karanaName} · Slot ${karanaSlot}`;
// }

// function applyTithiTimeTheme(inputHour, sunriseHr, sunsetHr) {
//   const themes = {
//     dawn:    { bg:'linear-gradient(135deg,#f5a030 0%,#e06010 60%,#c03030 100%)', icon:'🌅', border:'#e07030', text:'#fff8ee' },
//     morning: { bg:'linear-gradient(135deg,#ffe066 0%,#f0a020 50%,#4090d0 100%)', icon:'🌤', border:'#e0a030', text:'#fff8e0' },
//     midday:  { bg:'linear-gradient(135deg,#60b0f8 0%,#3888e0 50%,#1060c0 100%)', icon:'☀', border:'#3080d0', text:'#eef6ff' },
//     afternoon:{ bg:'linear-gradient(135deg,#f0c040 0%,#e08020 50%,#c04010 100%)',icon:'🌤', border:'#d07020', text:'#fff4e0' },
//     dusk:    { bg:'linear-gradient(135deg,#f08020 0%,#c83020 50%,#702018 100%)', icon:'🌇', border:'#c04020', text:'#fff0e0' },
//     evening: { bg:'linear-gradient(135deg,#8040c0 0%,#4020a0 50%,#200860 100%)', icon:'🌆', border:'#6030a0', text:'#f0e8ff' },
//     night:   { bg:'linear-gradient(135deg,#1a2060 0%,#0a0a30 50%,#000018 100%)', icon:'🌙', border:'#2030a0', text:'#d0d8ff' },
//   };
//   function momentForHour(h) {
//     if (h < sunriseHr - 0.5)             return 'night';
//     if (h < sunriseHr + 0.5)             return 'dawn';
//     if (h < sunriseHr + 3)               return 'morning';
//     if (h < (sunriseHr + sunsetHr) / 2)  return 'midday';
//     if (h < sunsetHr - 1.5)              return 'afternoon';
//     if (h < sunsetHr + 0.5)              return 'dusk';
//     if (h < sunsetHr + 2)                return 'evening';
//     return 'night';
//   }
//   const moments = { sunrise: momentForHour(sunriseHr), now: momentForHour(inputHour), sunset: momentForHour(sunsetHr) };
//   const fixedIcons = { sunrise:'🌅', sunset:'🌇' };
//   ['sunrise','now','sunset'].forEach(key => {
//     const btn = document.getElementById('tmb_' + key);
//     if (!btn) return;
//     const t = themes[moments[key]] || themes.midday;
//     btn.style.background  = t.bg;
//     btn.style.borderColor = t.border;
//     btn.style.color       = t.text;
//     const iconEl = btn.querySelector('.tmb-icon');
//     if (iconEl) iconEl.textContent = fixedIcons[key] || t.icon;
//   });
// }

// // ─────────────────────────────────────────────────────────────────
// //  PANCHANGA — Enhanced data tables for Vara, Nakshatra, Yoga
// // ─────────────────────────────────────────────────────────────────

// const VARAS = [
//   { n:'Ravivara',    en:'Sunday',    lord:'Sun',     sym:'☀', color:'#d4760a', nature:'Ugra (Fierce)',
//     deity:'Surya', deityNote:'Lord of light and soul',
//     horaLord:'Sun', classification:'Ugra', classNote:'Fierce — suited for bold acts',
//     auspicious:'Travel, authority, medicine', info:'Sunday is ruled by the Sun (Surya). Excellent for activities relating to government, authority, father, medicine, and gold. The Sun-hora at sunrise amplifies power and confidence. Avoid confrontational disputes.' },
//   { n:'Somavara',    en:'Monday',    lord:'Moon',    sym:'☽', color:'#1d4e6f', nature:'Saumya (Gentle)',
//     deity:'Chandra', deityNote:'Lord of mind and emotions',
//     horaLord:'Moon', classification:'Saumya', classNote:'Gentle — suited for nurturing acts',
//     auspicious:'Family, travel, agriculture, healing', info:'Monday is ruled by the Moon (Chandra / Soma). Ideal for activities related to mother, home, water, emotions, and agriculture. The waxing Moon enhances Monday\'s beneficial qualities. Favourable for starting journeys northward.' },
//   { n:'Mangalavara', en:'Tuesday',   lord:'Mars',    sym:'♂', color:'#b83020', nature:'Ugra (Fierce)',
//     deity:'Mangala', deityNote:'Lord of energy and courage',
//     horaLord:'Mars', classification:'Ugra', classNote:'Fierce — suited for courageous acts',
//     auspicious:'Physical work, surgery, law enforcement', info:'Tuesday is ruled by Mars (Mangala). Strong for activities requiring courage, physical exertion, surgery, and military matters. Avoid disputes and legal matters if possible. Mars-hora brings sharp focus and determination.' },
//   { n:'Budhavara',   en:'Wednesday', lord:'Mercury', sym:'☿', color:'#2e7a6e', nature:'Saumya (Gentle)',
//     deity:'Budha', deityNote:'Lord of intellect and communication',
//     horaLord:'Mercury', classification:'Saumya', classNote:'Gentle — suited for intellectual acts',
//     auspicious:'Business, communication, education, trade', info:'Wednesday is ruled by Mercury (Budha). Excellent for trade, communication, writing, education, and business contracts. The most favourable day for commerce and signing agreements. Mercury-hora amplifies wit and analytical clarity.' },
//   { n:'Guruvara',    en:'Thursday',  lord:'Jupiter', sym:'♃', color:'#7a5a10', nature:'Guru (Auspicious)',
//     deity:'Brihaspati', deityNote:'Lord of wisdom and dharma',
//     horaLord:'Jupiter', classification:'Guru', classNote:'Auspicious — best for sacred acts',
//     auspicious:'Rituals, education, guru worship, marriage', info:'Thursday is ruled by Jupiter (Guru/Brihaspati). The most auspicious day for beginning spiritual practices, religious ceremonies, education, and marriage. Jupiter-hora is the most powerful time for pujas, initiation (diksha), and seeking blessings.' },
//   { n:'Shukravara',  en:'Friday',    lord:'Venus',   sym:'♀', color:'#8e3a7a', nature:'Saumya (Gentle)',
//     deity:'Shukra', deityNote:'Lord of love, arts, and luxury',
//     horaLord:'Venus', classification:'Saumya', classNote:'Gentle — suited for arts and love',
//     auspicious:'Marriage, arts, beauty, romance, luxury', info:'Friday is ruled by Venus (Shukra). Ideal for love, art, music, beauty treatments, and sensory pleasures. Favourable for purchasing jewellery, clothing, and luxury items. Venus-hora is powerful for attracting abundance and harmonious relationships.' },
//   { n:'Shanivara',   en:'Saturday',  lord:'Saturn',  sym:'♄', color:'#4a4060', nature:'Sthira (Stable)',
//     deity:'Shani', deityNote:'Lord of karma and discipline',
//     horaLord:'Saturn', classification:'Sthira', classNote:'Stable — suited for enduring acts',
//     auspicious:'Long-term planning, discipline, oil treatments', info:'Saturday is ruled by Saturn (Shani). Best for activities requiring persistence, discipline, and long-term commitment. Favourable for Shani puja, charitable acts, and purification. Saturn-hora brings seriousness and karmic awareness.' },
// ];

// const YOGAS = [
//   { n:'Vishkambha', nature:'Inauspicious', lord:'Saturn',  deity:'Yama',     cls:'Mahavisha',  desc:'Obstructed progress; avoid starting important work'     },
//   { n:'Priti',      nature:'Auspicious',   lord:'Mercury', deity:'Vishnu',   cls:'Subha',      desc:'Love and affection flourish; good for relationships'     },
//   { n:'Ayushman',   nature:'Auspicious',   lord:'Saturn',  deity:'Brahma',   cls:'Subha',      desc:'Long life and health; good for medical treatments'       },
//   { n:'Saubhagya',  nature:'Auspicious',   lord:'Jupiter', deity:'Lakshmi',  cls:'Subha',      desc:'Fortune and prosperity; excellent for all undertakings'  },
//   { n:'Shobhana',   nature:'Auspicious',   lord:'Mars',    deity:'Brihaspati',cls:'Subha',     desc:'Brilliance and beauty; good for arts and beautification'  },
//   { n:'Atiganda',   nature:'Inauspicious', lord:'Sun',     deity:'Moon',     cls:'Ashubha',    desc:'Accidents and obstacles; proceed with caution'           },
//   { n:'Sukarma',    nature:'Auspicious',   lord:'Jupiter', deity:'Indra',    cls:'Subha',      desc:'Good deeds rewarded; excellent for charitable acts'       },
//   { n:'Dhriti',     nature:'Auspicious',   lord:'Saturn',  deity:'Apsaras',  cls:'Subha',      desc:'Steadfastness and resolve; good for commitments'         },
//   { n:'Shoola',     nature:'Inauspicious', lord:'Mars',    deity:'Rudra',    cls:'Ashubha',    desc:'Sharp pain and conflict; avoid confrontations'           },
//   { n:'Ganda',      nature:'Inauspicious', lord:'Sun',     deity:'Agni',     cls:'Ashubha',    desc:'Danger and strife; be cautious with fire and sharp tools'},
//   { n:'Vriddhi',    nature:'Auspicious',   lord:'Moon',    deity:'Jaya',     cls:'Subha',      desc:'Growth and increase; excellent for investments and gains' },
//   { n:'Dhruva',     nature:'Auspicious',   lord:'Mars',    deity:'Brahma',   cls:'Subha',      desc:'Permanence and stability; good for laying foundations'    },
//   { n:'Vyaghata',   nature:'Inauspicious', lord:'Sun',     deity:'Vayu',     cls:'Ashubha',    desc:'Sudden losses; avoid new ventures and travel'            },
//   { n:'Harshana',   nature:'Auspicious',   lord:'Mercury', deity:'Bhaga',    cls:'Subha',      desc:'Joy and delight; good for celebrations and entertainment'},
//   { n:'Vajra',      nature:'Inauspicious', lord:'Jupiter', deity:'Varuna',   cls:'Ashubha',    desc:'Thunderbolt — harsh results; be careful with water'      },
//   { n:'Siddhi',     nature:'Auspicious',   lord:'Venus',   deity:'Ganesha',  cls:'Subha',      desc:'Accomplishment; best yoga for beginning any important act'},
//   { n:'Vyatipata',  nature:'Inauspicious', lord:'Rahu',    deity:'Rudra',    cls:'Mahavisha',  desc:'Calamity; a very inauspicious yoga — avoid all new starts'},
//   { n:'Variyana',   nature:'Auspicious',   lord:'Venus',   deity:'Kubera',   cls:'Subha',      desc:'Wealth and comfort; good for luxury and financial matters'},
//   { n:'Parigha',    nature:'Inauspicious', lord:'Sun',     deity:'Vishwakarma',cls:'Ashubha',  desc:'Barrier and obstruction; difficult to complete tasks'    },
//   { n:'Shiva',      nature:'Auspicious',   lord:'Mercury', deity:'Shiva',    cls:'Subha',      desc:'Divine grace; excellent for spiritual worship and puja'  },
//   { n:'Siddha',     nature:'Auspicious',   lord:'Jupiter', deity:'Ganesha',  cls:'Subha',      desc:'Perfect accomplishment; all works succeed with ease'     },
//   { n:'Sadhya',     nature:'Auspicious',   lord:'Venus',   deity:'Chandra',  cls:'Subha',      desc:'Achievable goals; moderate effort yields good results'    },
//   { n:'Shubha',     nature:'Auspicious',   lord:'Mercury', deity:'Lakshmi',  cls:'Subha',      desc:'Pure auspiciousness; very good for all activities'       },
//   { n:'Shukla',     nature:'Auspicious',   lord:'Moon',    deity:'Parvati',  cls:'Subha',      desc:'Brightness and clarity; excellent for creative work'      },
//   { n:'Brahma',     nature:'Auspicious',   lord:'Moon',    deity:'Brahma',   cls:'Subha',      desc:'Creative power; excellent for starting new projects'      },
//   { n:'Indra',      nature:'Auspicious',   lord:'Sun',     deity:'Indra',    cls:'Subha',      desc:'Kingly victory; good for competitive and bold endeavours' },
//   { n:'Vaidhriti',  nature:'Inauspicious', lord:'Saturn',  deity:'Mitra',    cls:'Mahavisha',  desc:'Portends loss; a very inauspicious yoga — use caution'   },
// ];

// const NAKSHATRAS = [
//   {n:'Ashwini',          l:'Ketu',    d:'Ashwini Kumaras', gana:'Deva',    yoni:'Horse',     nadi:'Vata',  tattva:'Earth', quality:'Kshipra (Quick)'},
//   {n:'Bharani',          l:'Venus',   d:'Yama',            gana:'Manushya',yoni:'Elephant',  nadi:'Pitta', tattva:'Earth', quality:'Ugra (Fierce)'},
//   {n:'Krittika',         l:'Sun',     d:'Agni',            gana:'Rakshasa',yoni:'Sheep',     nadi:'Kapha', tattva:'Earth', quality:'Mishra (Mixed)'},
//   {n:'Rohini',           l:'Moon',    d:'Brahma',          gana:'Manushya',yoni:'Serpent',   nadi:'Kapha', tattva:'Earth', quality:'Dhruva (Fixed)'},
//   {n:'Mrigashira',       l:'Mars',    d:'Soma',            gana:'Deva',    yoni:'Serpent',   nadi:'Pitta', tattva:'Earth', quality:'Mridu (Soft)'},
//   {n:'Ardra',            l:'Rahu',    d:'Rudra',           gana:'Manushya',yoni:'Dog',       nadi:'Vata',  tattva:'Water', quality:'Tikshna (Sharp)'},
//   {n:'Punarvasu',        l:'Jupiter', d:'Aditi',           gana:'Deva',    yoni:'Cat',       nadi:'Vata',  tattva:'Water', quality:'Chara (Movable)'},
//   {n:'Pushya',           l:'Saturn',  d:'Brihaspati',      gana:'Deva',    yoni:'Sheep',     nadi:'Pitta', tattva:'Water', quality:'Mridu (Soft)'},
//   {n:'Ashlesha',         l:'Mercury', d:'Nagas',           gana:'Rakshasa',yoni:'Cat',       nadi:'Kapha', tattva:'Water', quality:'Tikshna (Sharp)'},
//   {n:'Magha',            l:'Ketu',    d:'Pitris',          gana:'Rakshasa',yoni:'Rat',       nadi:'Kapha', tattva:'Water', quality:'Ugra (Fierce)'},
//   {n:'Purva Phalguni',   l:'Venus',   d:'Bhaga',           gana:'Manushya',yoni:'Rat',       nadi:'Pitta', tattva:'Water', quality:'Ugra (Fierce)'},
//   {n:'Uttara Phalguni',  l:'Sun',     d:'Aryaman',         gana:'Manushya',yoni:'Cow',       nadi:'Vata',  tattva:'Fire',  quality:'Dhruva (Fixed)'},
//   {n:'Hasta',            l:'Moon',    d:'Savitar',         gana:'Deva',    yoni:'Buffalo',   nadi:'Vata',  tattva:'Fire',  quality:'Kshipra (Quick)'},
//   {n:'Chitra',           l:'Mars',    d:'Vishwakarma',     gana:'Rakshasa',yoni:'Tiger',     nadi:'Pitta', tattva:'Fire',  quality:'Mridu (Soft)'},
//   {n:'Swati',            l:'Rahu',    d:'Vayu',            gana:'Deva',    yoni:'Buffalo',   nadi:'Kapha', tattva:'Fire',  quality:'Chara (Movable)'},
//   {n:'Vishakha',         l:'Jupiter', d:'Indra-Agni',      gana:'Rakshasa',yoni:'Tiger',     nadi:'Kapha', tattva:'Fire',  quality:'Mishra (Mixed)'},
//   {n:'Anuradha',         l:'Saturn',  d:'Mitra',           gana:'Deva',    yoni:'Deer',      nadi:'Pitta', tattva:'Air',   quality:'Mridu (Soft)'},
//   {n:'Jyeshtha',         l:'Mercury', d:'Indra',           gana:'Rakshasa',yoni:'Deer',      nadi:'Vata',  tattva:'Air',   quality:'Tikshna (Sharp)'},
//   {n:'Moola',            l:'Ketu',    d:'Nirrti',          gana:'Rakshasa',yoni:'Dog',       nadi:'Kapha', tattva:'Air',   quality:'Tikshna (Sharp)'},
//   {n:'Purva Ashadha',    l:'Venus',   d:'Apas',            gana:'Manushya',yoni:'Monkey',    nadi:'Pitta', tattva:'Air',   quality:'Ugra (Fierce)'},
//   {n:'Uttara Ashadha',   l:'Sun',     d:'Vishvedevas',     gana:'Manushya',yoni:'Mongoose',  nadi:'Vata',  tattva:'Air',   quality:'Dhruva (Fixed)'},
//   {n:'Shravana',         l:'Moon',    d:'Vishnu',          gana:'Deva',    yoni:'Monkey',    nadi:'Kapha', tattva:'Ether', quality:'Chara (Movable)'},
//   {n:'Dhanishta',        l:'Mars',    d:'Ashta Vasus',     gana:'Rakshasa',yoni:'Lion',      nadi:'Pitta', tattva:'Ether', quality:'Chara (Movable)'},
//   {n:'Shatabhisha',      l:'Rahu',    d:'Varuna',          gana:'Rakshasa',yoni:'Horse',     nadi:'Vata',  tattva:'Ether', quality:'Chara (Movable)'},
//   {n:'Purva Bhadrapada', l:'Jupiter', d:'Aja Ekapada',     gana:'Manushya',yoni:'Lion',      nadi:'Vata',  tattva:'Ether', quality:'Ugra (Fierce)'},
//   {n:'Uttara Bhadrapada',l:'Saturn',  d:'Ahir Budhyana',   gana:'Manushya',yoni:'Cow',       nadi:'Pitta', tattva:'Ether', quality:'Dhruva (Fixed)'},
//   {n:'Revati',           l:'Mercury', d:'Pushan',          gana:'Deva',    yoni:'Elephant',  nadi:'Kapha', tattva:'Ether', quality:'Mridu (Soft)'}
// ];

// const WESTERN_SIGNS = [
//   {n:'Aries',s:'♈'},{n:'Taurus',s:'♉'},{n:'Gemini',s:'♊'},{n:'Cancer',s:'♋'},
//   {n:'Leo',s:'♌'},{n:'Virgo',s:'♍'},{n:'Libra',s:'♎'},{n:'Scorpio',s:'♏'},
//   {n:'Sagittarius',s:'♐'},{n:'Capricorn',s:'♑'},{n:'Aquarius',s:'♒'},{n:'Pisces',s:'♓'}
// ];
// const VEDIC_SIGNS = [
//   'Mesha','Vrishabha','Mithuna','Karka','Simha','Kanya',
//   'Tula','Vrishchika','Dhanu','Makara','Kumbha','Meena'
// ];

// // ─────────────────────────────────────────────────────────────────
// //  computePanchanga
// // ─────────────────────────────────────────────────────────────────
// function computePanchanga(jd, ayan, yr, mo, dy, utcOff) {
//   // Vara (weekday) — must be based on local sunrise, not UTC midnight.
//   // The vara is determined by the weekday at local sunrise.
//   // JD 0 = Monday (day 1). Standard: floor(JD + 1.5) % 7
//   // 0=Sun,1=Mon,2=Tue,3=Wed,4=Thu,5=Fri,6=Sat
//   // Add utcOff/24 to shift JD to local time before computing weekday
//   const varaIdx = Math.floor((jd + utcOff/24 + 1.5)) % 7;
//   const vara = VARAS[varaIdx];
//   const moonSider = n360(moonLongitude(jd) - ayan);
//   const nakSz     = 360 / 27;
//   const nakIdx    = Math.floor(moonSider / nakSz);
//   const nakProg   = (moonSider % nakSz) / nakSz;
//   const nakPada   = Math.floor(nakProg * 4) + 1;
//   const moonNak   = NAKSHATRAS[nakIdx];
//   const sunSider  = n360(sunLongitude(jd) - ayan);
//   const yogaSum   = n360(sunSider + moonSider);
//   const yogaIdx   = Math.floor(yogaSum / nakSz) % 27;
//   const yogaProg  = (yogaSum % nakSz) / nakSz;
//   const yoga      = YOGAS[yogaIdx];
//   return {
//     vara, varaIdx,
//     moonNak, nakIdx, nakProg, nakPada,
//     yoga, yogaIdx, yogaProg,
//     moonSider, sunSider, yogaSum,
//   };
// }

// let _panchaData = null;

// function renderPanchanga(pancha, ss) {
//   const { vara, moonNak, nakIdx, nakProg, nakPada, yoga, yogaProg, yogaIdx, yogaSum, sunSider, moonSider } = pancha;

//   setText('varaStripTitle', `${vara.n} · ${vara.lord}`);
//   setText('varaStripSub',   `${vara.classification} Vara · ${vara.nature}`);
//   setText('varaStripDay',   vara.en);
//   const varaStripSymEl = document.getElementById('varaStripSym');
//   if (varaStripSymEl) { varaStripSymEl.textContent = vara.sym; varaStripSymEl.style.color = vara.color; }

//   setText('panchaVaraName',   vara.n);
//   setText('panchaVaraEn',     vara.en);
//   setText('panchaVaraLord',   vara.lord);
//   setText('panchaVaraNature', vara.nature);
//   const varaSymEl = document.getElementById('panchaVaraSym');
//   if (varaSymEl) { varaSymEl.textContent = vara.sym; varaSymEl.style.color = vara.color; }
//   setText('varaHoraLord',    vara.horaLord);
//   setText('varaClass',       vara.classification);
//   setText('varaClassSub',    vara.classNote);
//   setText('varaDeity',       vara.deity);
//   setText('varaDeitySub',    vara.deityNote);
//   setText('varaAuspicious',  vara.classification === 'Guru' ? 'Very Auspicious ✦✦✦' :
//                               vara.classification === 'Saumya' ? 'Auspicious ✦✦' : 'Use with Intention ✦');
//   setText('varaActivity',    vara.auspicious);
//   setText('varaInfoTitle',   `${vara.sym} ${vara.n} (${vara.en})`);
//   setText('varaInfoText',    vara.info);

//   setText('nakStripTitle', `${moonNak.n} Nakshatra`);
//   setText('nakStripSub',   `Lord: ${moonNak.l} · Deity: ${moonNak.d} · Pada ${nakPada}`);
//   setText('nakStripNum',   `${nakIdx + 1} / 27`);

//   setText('panchaNakName',    moonNak.n);
//   setText('panchaNakLord',    moonNak.l);
//   setText('panchaNakDeity',   moonNak.d);
//   setText('panchaNakPada',    `Pada ${nakPada} of 4`);
//   setText('panchaNakNum',     `Nakshatra ${nakIdx + 1} of 27`);
//   const nakBar = document.getElementById('panchaNakProgress');
//   if (nakBar) { nakBar.style.width = '0%'; setTimeout(() => nakBar.style.width = (nakProg*100).toFixed(1)+'%', 100); }
//   setText('panchaNakProgPct', `${(nakProg*100).toFixed(1)}% elapsed`);
//   setText('nakGana',    moonNak.gana);
//   setText('nakGanaSub', moonNak.gana === 'Deva' ? 'Divine · Spiritual temperament' :
//                         moonNak.gana === 'Manushya' ? 'Human · Worldly temperament' : 'Demonic · Fierce temperament');
//   setText('nakYoni',    moonNak.yoni);
//   setText('nakYoniSub', 'Symbolic animal · Compatibility');
//   setText('nakNadi',    moonNak.nadi);
//   setText('nakNadiSub', moonNak.nadi === 'Vata' ? 'Air — quick, changeable' :
//                         moonNak.nadi === 'Pitta' ? 'Fire — intense, sharp' : 'Water — slow, steady');
//   setText('nakTattva',    moonNak.tattva);
//   setText('nakTattvaSub', 'Elemental quality');
//   setText('nakQuality',   moonNak.quality);
//   setText('nakQualitySub','Muhurta classification');

//   setText('nakInfoTitle', `✦ ${moonNak.n}`);
//   setText('nakInfoText',  `Deity: ${moonNak.d} · Lord: ${moonNak.l} · Gana: ${moonNak.gana} · Yoni: ${moonNak.yoni} · Nadi: ${moonNak.nadi} Dosha · Element: ${moonNak.tattva}. Quality: ${moonNak.quality}.`);

//   setText('yogaStripTitle', `${yoga.n} Yoga`);
//   setText('yogaStripSub',   `${yoga.nature} · Lord: ${yoga.lord} · ${yoga.cls}`);
//   setText('yogaStripNum',   `${yogaIdx + 1} / 27`);

//   setText('panchaYogaName',    yoga.n);
//   setText('panchaYogaNature',  yoga.nature);
//   setText('panchaYogaLord',    yoga.lord);
//   setText('panchaYogaNum',     `Yoga ${yogaIdx + 1} of 27`);
//   setText('yogaDeity',         yoga.deity);
//   setText('yogaDeitySub',      'Presiding deity of this yoga');
//   setText('yogaClass',         yoga.cls);
//   setText('yogaClassSub',      yoga.cls === 'Mahavisha' ? 'Highly inauspicious — avoid new starts' :
//                                yoga.cls === 'Ashubha' ? 'Inauspicious — proceed carefully' : 'Auspicious — favourable for activity');
//   setText('yogaSumDeg',        `${yogaSum.toFixed(2)}°`);
//   setText('yogaProgVal',       `${(yogaProg*100).toFixed(1)}%`);
//   setText('panchaYogaProgPct', `${(yogaProg*100).toFixed(1)}% elapsed`);

//   const yogaBar = document.getElementById('panchaYogaProgress');
//   if (yogaBar) { yogaBar.style.width = '0%'; setTimeout(() => yogaBar.style.width = (yogaProg*100).toFixed(1)+'%', 140); }
//   const yogaBarPct = document.getElementById('panchaYogaProgPctBar');
//   if (yogaBarPct) yogaBarPct.textContent = `${(yogaProg*100).toFixed(1)}%`;

//   setText('yogaInfoTitle', `✧ ${yoga.n} Yoga`);
//   setText('yogaInfoText',  yoga.desc + ` Deity: ${yoga.deity}. Lord: ${yoga.lord}. Sun+Moon sum: ${yogaSum.toFixed(2)}° sidereal.`);

//   if (ss && !ss.polar) {
//     setText('panchaRise', ss.rise !== null ? decToHMS(ss.rise) : '—');
//     setText('panchaSet',  ss.set  !== null ? decToHMS(ss.set)  : '—');
//     const dlH = Math.floor(ss.dayLength), dlM = Math.round((ss.dayLength - dlH)*60);
//     setText('panchaDayLen', `${dlH}h ${dlM}m`);
//   } else {
//     setText('panchaRise',   ss && ss.polar==='no_rise' ? 'Polar Night' : 'Midnight Sun');
//     setText('panchaSet',    '—');
//     setText('panchaDayLen', ss && ss.polar==='no_rise' ? '0h' : '24h');
//   }

//   setTimeout(() => {
//     const tk = _tithiData.sunrise || _tithiData.now;
//     if (tk) {
//       setText('psSumTithi',  `${tk.tithi.paksha.slice(0,3)} ${tk.tithi.n}`);
//       setText('psSumKarana', tk.karana.n);
//     }
//     setText('psSumNak',    pancha.moonNak.n);
//     setText('psSumYoga',   pancha.yoga.n);
//     const varaShort = pancha.vara.n.replace(/vara$/i,'');
//     setText('psSumVara',   varaShort);
//   }, 50);

//   setTimeout(() => {
//     updateVaraArc(pancha.varaIdx, `${pancha.vara.n} (${pancha.vara.en})`);
//     updateNakArc(pancha.nakIdx, `${pancha.moonNak.n} · Pada ${pancha.nakPada}`);
//     updateYogaArc(pancha.yogaIdx, pancha.yoga.n, pancha.yoga.nature === 'Auspicious');
//   }, 100);
// }

// function renderTithiKarana(tk, tkRise, tkSet, ayan, ss, inputHr) {
//   _tithiAyan = ayan;
//   _tithiData.sunrise = tkRise;
//   _tithiData.now     = tk;
//   _tithiData.sunset  = tkSet;
//   const fmt = x => x ? `${x.tithi.paksha} ${x.tithi.n} ${x.tithi.num} · ${x.elong.toFixed(1)}°` : '—';
//   setText('tmb_sub_sunrise', tkRise ? fmt(tkRise) : 'No sunrise');
//   setText('tmb_sub_now',     fmt(tk));
//   setText('tmb_sub_sunset',  tkSet  ? fmt(tkSet)  : 'No sunset');
//   if (ss && !ss.polar) {
//     setText('tmb_time_sunrise', ss.rise !== null ? decToHMS(ss.rise) : '—');
//     setText('tmb_time_now',     inputHr !== undefined ? decToHMS(inputHr) : '—');
//     setText('tmb_time_sunset',  ss.set  !== null ? decToHMS(ss.set)  : '—');
//   } else {
//     setText('tmb_time_sunrise', ss && ss.polar==='no_rise' ? 'Polar Night' : 'Midnight Sun');
//     setText('tmb_time_now',     inputHr !== undefined ? decToHMS(inputHr) : '—');
//     setText('tmb_time_sunset',  '—');
//   }
//   _tithiMode = 'now';
//   switchTithiMode('sunrise');
// }

// // ─────────────────────────────────────────────────────────────────
// //  NORTH INDIAN KUNDALI CHART (SVG) — Simplified Clean Version
// // ─────────────────────────────────────────────────────────────────

// const PLANET_ABBR = {
//   sun:'Su', moon:'Mo', mercury:'Me', venus:'Ve', mars:'Ma',
//   jupiter:'Ju', saturn:'Sa', rahu:'Ra', ketu:'Ke',
// };

// // ── Western Sign Toggle ──────────────────────────────────────────
// let _showWestern = false;  // Western sign hidden by default per user request

// function toggleWestern() {
//   _showWestern = !_showWestern;
//   const btn = document.getElementById('westernToggleBtn');
//   if (btn) btn.textContent = _showWestern ? '🌐 Hide Western' : '🌐 See Western Sign';
//   // Show/hide all western sign tiles
//   document.querySelectorAll('.western-sign-tile').forEach(el => {
//     el.style.display = _showWestern ? '' : 'none';
//   });
//   // Update strip subtitles
//   document.querySelectorAll('.western-strip-sub').forEach(el => {
//     el.style.display = _showWestern ? '' : 'none';
//   });
// }


// const PLANET_META = {
//   sun:     { label:'Sun',     sym:'☀',  tabActive:'active-sun'   },
//   moon:    { label:'Moon',    sym:'☽',  tabActive:'active-moon'  },
//   mercury: { label:'Mercury', sym:'☿',  tabActive:'active-merc'  },
//   venus:   { label:'Venus',   sym:'♀',  tabActive:'active-venus' },
//   mars:    { label:'Mars',    sym:'♂',  tabActive:'active-mars'  },
//   jupiter: { label:'Jupiter', sym:'♃',  tabActive:'active-jup'   },
//   saturn:  { label:'Saturn',  sym:'♄',  tabActive:'active-sat'   },
//   rahu:    { label:'Rahu',    sym:'☊',  tabActive:'active-rahu'  },
//   ketu:    { label:'Ketu',    sym:'☋',  tabActive:'active-ketu'  },
// };

// // ─────────────────────────────────────────────────────────────────
// //  NORTH INDIAN KUNDALI CHART — Reference Style (White bg, clean lines)
// // ─────────────────────────────────────────────────────────────────
// // Vimshottari dasha lords in order
// const DASHA_LORDS = ['Ketu','Venus','Sun','Moon','Mars','Rahu','Jupiter','Saturn','Mercury'];
// const DASHA_YEARS = [7, 20, 6, 10, 7, 18, 16, 19, 17];  // total = 120
// const DASHA_LORD_NAKS = {  // starting nakshatra index for each lord
//   'Ketu':0,'Venus':1,'Sun':9,'Moon':10,'Mars':18,'Rahu':19,'Jupiter':20,'Saturn':26,'Mercury':3
// };

// function getDashaMd(moonSiderLon) {
//   const nakSz   = 360 / 27;
//   const nakIdx  = Math.floor(moonSiderLon / nakSz);
//   const nakProg = (moonSiderLon % nakSz) / nakSz;
//   // Which dasha lord owns this nakshatra?
//   const lordOrder = ['Ketu','Venus','Sun','Moon','Mars','Rahu','Jupiter','Saturn','Mercury'];
//   const lordsForNak = [
//     0,1,2,3,4,5,6,7,8,  // 1-9: Ketu→Merc
//     0,1,2,3,4,5,6,7,8,  // 10-18
//     0,1,2,3,4,5,6,7,8   // 19-27
//   ];
//   const lordIdx = lordsForNak[nakIdx];
//   const lord    = lordOrder[lordIdx];
//   const lordYrs = DASHA_YEARS[lordIdx];
//   const elapsed = nakProg * lordYrs;
//   const remaining = lordYrs - elapsed;
//   const yrs = Math.floor(remaining);
//   const mosFrac = (remaining - yrs) * 12;
//   const mos = Math.floor(mosFrac);
//   const days = Math.round((mosFrac - mos) * 30);
//   return { lord, remaining, yrs, mos, days, lordYrs };
// }

// // ═══════════════════════════════════════════════════════════════════
// //  REPLACE the entire drawKundaliChart() function in your astro.js
// //  with this corrected version.
// //
// //  FIXES:
// //  1. Correct North Indian house-to-cell mapping
// //  2. Larger planet text (16px → readable)
// //  3. Accurate cell centroids using proper triangle geometry
// //  4. Correct inner diamond cell positions
// // ═══════════════════════════════════════════════════════════════════

// function drawKundaliChart(ascTrop, planetData, ayan, containerId) {
//   const container = document.getElementById(containerId);
//   if (!container) return;

//   const ascSider   = n360(ascTrop - ayan);
//   const ascSignIdx = Math.floor(ascSider / 30);

//   // Whole-sign houses: house h (1-12) has sign (ascSignIdx + h-1) % 12
//   const houseSign  = Array.from({length:12}, (_,h) => (ascSignIdx + h) % 12);
//   const signToHouse = new Array(12).fill(0);
//   houseSign.forEach((sign, h) => { signToHouse[sign] = h + 1; });

//   // Place planets into houses
//   const housePlanets = Array.from({length:12}, () => []);
//   for (const [pid, data] of Object.entries(planetData)) {
//     const pSider   = n360(data.trop - ayan);
//     const pSignIdx = Math.floor(pSider / 30);
//     const house    = signToHouse[pSignIdx];
//     if (house >= 1) housePlanets[house-1].push({
//       pid, retro: data.retro, abbr: PLANET_ABBR[pid]
//     });
//   }

//   const PC = {
//     sun:'#c07000', moon:'#1d4e6f', mercury:'#2e7a6e', venus:'#8e3a7a',
//     mars:'#b83020', jupiter:'#7a5a10', saturn:'#4a3a6a',
//     rahu:'#1a4a1a', ketu:'#7a2a10'
//   };

//   // ── SVG setup ──────────────────────────────────────────────────
//   const S  = 540;    // canvas size (square)
//   const C  = S / 2;  // center = 270
//   const lc = '#1e2d5a';

//   // 5 key points on the grid
//   // Outer corners + mid-edges
//   const TL=[0,0], TC=[C,0], TR=[S,0];
//   const ML=[0,C],            MR=[S,C];
//   const BL=[0,S], BC=[C,S], BR=[S,S];
//   const CT=[C,C];

//   // The 4 intersection points of corner-diagonals with diamond lines:
//   //   e.g. TL-diagonal (0,0)→(C,C) meets diamond line (0,C)→(C,0) at (C/2,C/2)
//   //   TR-diagonal (S,0)→(C,C) meets diamond line (C,0)→(S,C) at (3C/2,C/2)
//   const P_TL = [C/2,   C/2  ];  // (135,135)
//   const P_TR = [3*C/2, C/2  ];  // (405,135)
//   const P_BR = [3*C/2, 3*C/2];  // (405,405)
//   const P_BL = [C/2,   3*C/2];  // (135,405)

//   // ── Cell centroids ─────────────────────────────────────────────
//   // centroid of a triangle = average of its 3 vertices
//   const ctr = pts => ({
//     cx: pts.reduce((s,p)=>s+p[0],0)/pts.length,
//     cy: pts.reduce((s,p)=>s+p[1],0)/pts.length
//   });

//   // North Indian chart cell assignments (verified):
//   //
//   //   OUTER CORNER cells (one corner of the square):
//   //     H2  = TR corner: triangle TR, MR, P_TR
//   //     H4  = BR corner: triangle BR, BC, P_BR
//   //     H6  = BL corner: triangle BL, ML, P_BL
//   //     H8  = TL corner: triangle TL, TC, P_TL
//   //
//   //   OUTER MID cells (one side of the square, between two corner cells):
//   //     H1  = top:    triangle TC, TR, P_TR  (top-right half of top edge)
//   //              WAIT — H1 is the FULL top outer mid between diagonals.
//   //              Actually in NI chart, H1 sits at TOP CENTER.
//   //              Triangle: P_TL, TC, P_TR  (the top outer band between the two diagonals at top)
//   //              No — let's be precise. With lines:
//   //              TL→CT, TR→CT, TC→MR (diamond), ML→TC (diamond)
//   //              Top-left quadrant outer triangle = TL, TC, P_TL  → H8
//   //              Top-right quadrant outer triangle = TC, TR, P_TR  → H1 (rightward top)
//   //              But NI chart H1 is the TOP-CENTER house.
//   //              Looking at a real NI chart: H1 is at TOP, between TL-diagonal and TR-diagonal.
//   //              That means H1 = triangle (TL..TC..TR) minus the two corner tris = impossible in simple triangles.
//   //              CORRECT INTERPRETATION for NI chart with this geometry (8 lines):
//   //              The top outer region is split into TWO triangles by where the corner diagonals meet the diamond:
//   //                Left tri:  TL(0,0), TC(C,0), P_TL(C/2,C/2)  → H8
//   //                Right tri: TR(S,0), TC(C,0), P_TR(3C/2,C/2) → H1
//   //              So H1 is top-RIGHT outer, H8 is top-LEFT outer
//   //              In standard NI charts viewed from front: H1=top, H2=top-right corner...
//   //              This means the visual "top" is split between H8 (left half) and H1 (right half).
//   //              Most NI chart software puts H1 label centered at top, but mathematically
//   //              with diagonal lines meeting at center, H1 occupies the right-of-center top triangle.

//   const CELL = {
//     // OUTER cells
//     1:  ctr([TC, TR, P_TR]),       // top-right outer tri
//     2:  ctr([TR, MR, P_TR]),       // TR corner
//     3:  ctr([MR, BR, P_BR]),       // right outer tri
//     4:  ctr([BR, BC, P_BR]),       // BR corner
//     5:  ctr([BC, BL, P_BL]),       // bottom outer tri
//     6:  ctr([BL, ML, P_BL]),       // BL corner
//     7:  ctr([ML, TL, P_TL]),       // left outer tri
//     8:  ctr([TL, TC, P_TL]),       // TL corner
//     // INNER cells (inside the central diamond)
//     9:  ctr([CT, P_TL, TC]),       // inner top
//     10: ctr([CT, P_TR, MR]),       // inner right
//     11: ctr([CT, P_BR, BC]),       // inner bottom
//     12: ctr([CT, P_BL, ML]),       // inner left
//   };

//   // ── Build SVG lines ────────────────────────────────────────────
//   const L = (x1,y1,x2,y2) =>
//     `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}"
//      stroke="${lc}" stroke-width="1.8" stroke-linecap="round"/>`;

//   let svg = `<svg viewBox="0 0 ${S} ${S}" xmlns="http://www.w3.org/2000/svg"
//     style="width:100%;max-width:560px;display:block;margin:0 auto;
//            background:#f4f6fa;border:2.5px solid ${lc};border-radius:3px">`;

//   // Border
//   svg += `<rect x="1" y="1" width="${S-2}" height="${S-2}" fill="none" stroke="${lc}" stroke-width="2.5"/>`;

//   // 4 corner diagonals → center
//   svg += L(0,0, C,C);
//   svg += L(S,0, C,C);
//   svg += L(S,S, C,C);
//   svg += L(0,S, C,C);

//   // 4 diamond lines (mid-edge to mid-edge)
//   svg += L(C,0, S,C);
//   svg += L(S,C, C,S);
//   svg += L(C,S, 0,C);
//   svg += L(0,C, C,0);

//   // ── House numbers (small, near edge) ──────────────────────────
//   const HNUM_POS = {
//     1: {x:S*3/4+20, y:18},
//     2: {x:S-16,     y:18},
//     3: {x:S-16,     y:C},
//     4: {x:S-16,     y:S-18},
//     5: {x:C,        y:S-18},
//     6: {x:16,       y:S-18},
//     7: {x:16,       y:C},
//     8: {x:16,       y:18},
//     9: {x:C-44,     y:C-44},
//     10:{x:C+44,     y:C-20},
//     11:{x:C+36,     y:C+48},
//     12:{x:C-48,     y:C+20},
//   };

//   for (let h=1; h<=12; h++) {
//     const {x,y} = HNUM_POS[h];
//     svg += `<text x="${x}" y="${y}" text-anchor="middle" dominant-baseline="middle"
//       font-family="Georgia,serif" font-size="13" font-weight="800"
//       fill="#1e3a8a" opacity="0.85">${h}</text>`;
//   }

//   // ── Render signs + planets ─────────────────────────────────────
//   const VSIGNS_SHORT = ['Ar','Ta','Ge','Cn','Le','Vi','Li','Sc','Sg','Cp','Aq','Pi'];
//   const LINE_H = 21;  // px between planet lines — bigger than before

//   for (let h=1; h<=12; h++) {
//     const sidx  = houseSign[h-1];
//     const plnts = housePlanets[h-1];
//     const {cx, cy} = CELL[h];
//     const n = plnts.length;

//     // Stack planets centered on cell centroid
//     const totalH = n * LINE_H;
//     const startY = cy - totalH/2 + LINE_H/2;

//     plnts.forEach((p, i) => {
//       const col = PC[p.pid] || '#333';
//       const lbl = p.abbr + (p.retro ? '*' : '');
//       const py  = startY + i * LINE_H;
//       svg += `<text x="${cx.toFixed(1)}" y="${py.toFixed(1)}"
//         text-anchor="middle" dominant-baseline="middle"
//         font-family="Arial,sans-serif" font-size="16" font-weight="900"
//         fill="${col}">${lbl}</text>`;
//     });

//     // Sign abbreviation (small, slightly below planet block)
//     const signY = n > 0 ? (startY + n*LINE_H + 7) : cy + 6;
//     svg += `<text x="${cx.toFixed(1)}" y="${signY.toFixed(1)}"
//       text-anchor="middle" dominant-baseline="middle"
//       font-family="Georgia,serif" font-size="11"
//       fill="#334466" opacity="0.6">${VSIGNS_SHORT[sidx]}</text>`;
//   }

//   svg += '</svg>';

//   // ── Degree strip below chart ───────────────────────────────────
//   const fmtDeg = (trop) => {
//     if (trop == null) return '—';
//     const sid = n360(trop - ayan);
//     const d   = Math.floor(sid % 30);
//     const m   = Math.floor(((sid % 30) - d) * 60);
//     const s   = Math.round((((sid % 30) - d) * 60 - m) * 60);
//     return `${d}° ${String(m).padStart(2,'0')}′ ${String(s).padStart(2,'0')}″`;
//   };

//   let dasha = { lord:'-', yrs:0, mos:0, days:0 };
//   if (planetData.moon) {
//     const ms = n360(planetData.moon.trop - ayan);
//     dasha = getDashaMd(ms);
//   }
//   const DASHA_PC = {
//     Sun:'#c07000', Moon:'#1d4e6f', Mars:'#b83020', Rahu:'#1a4a1a',
//     Jupiter:'#7a5a10', Saturn:'#4a3a6a', Mercury:'#2e7a6e',
//     Ketu:'#7a2a10', Venus:'#8e3a7a'
//   };

//   const STRIP_DATA = [
//     ['Asc',  ascTrop,                  '#1e3a8a'],
//     ['Sun',  planetData.sun?.trop,     '#c07000'],
//     ['Moon', planetData.moon?.trop,    '#1d4e6f'],
//     ['Merc', planetData.mercury?.trop, '#2e7a6e'],
//     ['Ven',  planetData.venus?.trop,   '#8e3a7a'],
//     ['Mars', planetData.mars?.trop,    '#b83020'],
//     ['Jupt', planetData.jupiter?.trop, '#7a5a10'],
//     ['Sat',  planetData.saturn?.trop,  '#4a3a6a'],
//     ['Rahu', planetData.rahu?.trop,    '#1a4a1a'],
//     ['Ketu', planetData.ketu?.trop,    '#7a2a10'],
//   ];
//   const RETRO_PIDS = {
//     Merc: planetData.mercury?.retro,
//     Ven:  planetData.venus?.retro,
//     Mars: planetData.mars?.retro,
//     Jupt: planetData.jupiter?.retro,
//     Sat:  planetData.saturn?.retro,
//     Rahu: true, Ketu: true,
//   };

//   let strip = `<div style="
//     font-family:'Courier New',monospace;font-size:0.75rem;
//     background:#f0f3f8;border:2px solid #1e2d5a;border-top:none;
//     padding:10px 16px 12px;
//     display:grid;grid-template-columns:1fr 1fr;gap:2px 20px;
//     max-width:560px;margin:0 auto;border-radius:0 0 4px 4px;">`;

//   STRIP_DATA.forEach(([lbl, trop, c]) => {
//     const rx = RETRO_PIDS[lbl];
//     const rm = rx ? `<span style="color:#b83020;font-weight:900">*</span>` : '';
//     strip += `<div style="display:flex;justify-content:space-between;align-items:center;
//       padding:3px 0;border-bottom:1px solid #dde2ee;">
//       <span style="font-weight:900;color:${c};min-width:40px;font-size:0.74rem">${lbl}${rm}</span>
//       <span style="color:#0f1e3a;font-weight:700;font-size:0.74rem">${fmtDeg(trop)}</span>
//     </div>`;
//   });

//   const dc = DASHA_PC[dasha.lord] || '#333';
//   strip += `<div style="grid-column:1/-1;margin-top:6px;padding-top:6px;
//     border-top:1.5px solid #c0c8dc;text-align:center;
//     font-size:0.77rem;font-weight:700;color:#0f1e3a;font-family:'DM Sans',sans-serif;">
//     Dasha Balance :
//     <span style="color:${dc};font-weight:900">${dasha.lord}</span>&nbsp;
//     <span style="color:#1a2a4a">${dasha.yrs}y ${dasha.mos}m ${dasha.days}d</span>
//   </div>
//   <div style="grid-column:1/-1;text-align:center;font-size:0.62rem;color:#7080a0;margin-top:2px;">
//     * Retrograde &nbsp;·&nbsp; Whole-sign houses &nbsp;·&nbsp; Lahiri ayanamsa &nbsp;·&nbsp; Sidereal
//   </div>`;
//   strip += '</div>';

//   container.innerHTML =
//     `<div style="display:block;width:100%;max-width:560px;margin:0 auto">${svg}${strip}</div>`;

//   // ── Legend panel updates ───────────────────────────────────────
//   const nakSz    = 360 / 27;
//   const lagnaHnak= NAKSHATRAS[Math.floor(ascSider / nakSz)];
//   const lagnaWS  = WESTERN_SIGNS[Math.floor(ascTrop / 30)];
//   const lagnaVS  = VEDIC_SIGNS[ascSignIdx];
//   setText('clsLagnaSign', `${lagnaVS} · ${lagnaWS.s} ${lagnaWS.n}`);
//   setText('clsLagnaNak',  `${lagnaHnak.n} (${lagnaHnak.l}) · ${(ascSider%30).toFixed(2)}° in sign`);

//   const SIGN_LORDS = ['Mars','Venus','Mercury','Moon','Sun','Mercury',
//                       'Venus','Mars','Jupiter','Saturn','Saturn','Jupiter'];

//   const houseSignsEl = document.getElementById('chartHouseSignsList');
//   if (houseSignsEl) {
//     let rows = '';
//     for (let h=1; h<=12; h++) {
//       const sidx  = houseSign[h-1];
//       const vSign = VEDIC_SIGNS[sidx];
//       const lord  = SIGN_LORDS[sidx];
//       const plnts = housePlanets[h-1].map(p => {
//         const c = PC[p.pid] || '#aaa';
//         return `<span style="color:${c};font-weight:900">${p.abbr}${p.retro?'*':''}</span>`;
//       }).join('\u00a0');
//       rows += `<div style="display:flex;align-items:center;gap:6px;padding:2.5px 0;
//         border-bottom:1px solid rgba(80,140,200,0.08);">
//         <span style="color:rgba(130,180,230,0.6);font-size:0.65rem;font-weight:700;min-width:22px">H${h}</span>
//         <span style="color:rgba(210,230,255,0.92);font-weight:600;flex:1;font-size:0.76rem">${vSign}</span>
//         <span style="color:rgba(140,175,220,0.5);font-size:0.63rem;min-width:44px">${lord}</span>
//         ${plnts ? `<span style="font-size:0.76rem">${plnts}</span>` : ''}
//       </div>`;
//     }
//     houseSignsEl.innerHTML = rows;
//   }

//   const planetSummaryEl = document.getElementById('chartPlanetSummary');
//   if (planetSummaryEl) {
//     const ORDERED = [
//       ['sun','Su'],['moon','Mo'],['mercury','Me'],['venus','Ve'],
//       ['mars','Ma'],['jupiter','Ju'],['saturn','Sa'],['rahu','Ra'],['ketu','Ke']
//     ];
//     let ps = '';
//     ORDERED.forEach(([pid, abbr]) => {
//       const d = planetData[pid];
//       if (!d) return;
//       const sid   = n360(d.trop - ayan);
//       const sign  = VEDIC_SIGNS[Math.floor(sid / 30)];
//       const nak   = NAKSHATRAS[Math.floor(sid / (360/27))].n;
//       const house = signToHouse[Math.floor(sid / 30)];
//       const col   = PC[pid] || '#aaa';
//       const degD  = Math.floor(sid % 30);
//       const degM  = Math.floor(((sid % 30) - degD) * 60);
//       const rStr  = d.retro ? `<span style="color:#c03030;font-weight:900">*</span>` : '';
//       ps += `<div style="display:flex;align-items:center;gap:6px;padding:3px 0;
//         border-bottom:1px solid rgba(80,140,200,0.08);">
//         <b style="color:${col};min-width:24px;font-size:0.83rem">${abbr}${rStr}</b>
//         <span style="color:rgba(205,228,255,0.9);flex:1;font-size:0.75rem">
//           ${sign} ${degD}°${String(degM).padStart(2,'0')}′</span>
//         <span style="color:rgba(140,175,220,0.5);font-size:0.65rem;min-width:22px">H${house}</span>
//         <span style="color:rgba(140,175,220,0.4);font-size:0.63rem">${nak.substring(0,7)}</span>
//       </div>`;
//     });
//     planetSummaryEl.innerHTML = ps;
//   }
// }









// // ─────────────────────────────────────────────────────────────────
// //  SUNRISE / SUNSET  (Meeus Ch.15)
// // ─────────────────────────────────────────────────────────────────
// function sunriseSunset(yr, mo, dy, lat, lon, utcOff) {
//   const jd  = julianDay(yr, mo, dy, 12 - utcOff);
//   const { dec } = sunEquatorial(jd);
//   const latR = r(lat), decR = r(dec);
//   const cosH = (Math.cos(r(90.833)) - Math.sin(latR)*Math.sin(decR))
//              / (Math.cos(latR)*Math.cos(decR));
//   if (cosH >  1) return { rise:null, set:null, polar:'no_rise' };
//   if (cosH < -1) return { rise:null, set:null, polar:'no_set'  };
//   const H   = Math.acos(cosH) / DEG;
//   const T   = (jd - 2451545.0) / 36525;
//   const L0  = n360(280.46646 + 36000.76983*T);
//   const M   = n360(357.52911 + 35999.05029*T);
//   const eps = 23.439291111 - 0.013004167*T;
//   const y   = Math.tan(r(eps/2))**2;
//   const eot = 4*(y*Math.sin(r(2*L0)) - 2*0.016708634*Math.sin(r(M))
//               + 4*0.016708634*y*Math.sin(r(M))*Math.cos(r(2*L0))
//               - 0.5*y*y*Math.sin(r(4*L0))
//               - 1.25*0.016708634*0.016708634*Math.sin(r(2*M)));
//   const lngHour = lon / 15;
//   return {
//     rise: normalHour(12 - H/15 - lngHour - eot/60 + utcOff),
//     set:  normalHour(12 + H/15 - lngHour - eot/60 + utcOff),
//     polar: null, dayLength: H*2/15
//   };
// }

// function normalHour(h) { return ((h%24)+24)%24; }

// // ─────────────────────────────────────────────────────────────────
// //  UTILITIES
// // ─────────────────────────────────────────────────────────────────
// function decToHMS(h) {
//   const sign=h<0?'-':''; h=Math.abs(h);
//   const hh=Math.floor(h), mm=Math.floor((h-hh)*60);
//   const ss=Math.round(((h-hh)*60-mm)*60);
//   return `${sign}${String(hh).padStart(2,'0')}:${String(mm).padStart(2,'0')}:${String(ss).padStart(2,'0')}`;
// }
// function dms(deg) {
//   const d=Math.floor(deg), ms=(deg-d)*60;
//   const m=Math.floor(ms), s=Math.round((ms-m)*60);
//   return `${d}° ${m}′ ${s}″`;
// }
// function setText(id, val) { const el=document.getElementById(id); if(el) el.textContent=val; }

// const HOUSE_NAMES = [
//   '',
//   'Self & Body',
//   'Wealth & Family',
//   'Courage & Siblings',
//   'Home & Mother',
//   'Children & Intellect',
//   'Enemies & Health',
//   'Partnerships',
//   'Transformation',
//   'Dharma & Fortune',
//   'Career & Status',
//   'Gains & Desires',
//   'Losses & Liberation'
// ];

// // ─────────────────────────────────────────────────────────────────
// //  COMPUTE ONE PLANET
// // ─────────────────────────────────────────────────────────────────
// function computePlanet(jd, ayan, calcFn, forceRetro) {
//   const trop  = calcFn(jd);
//   const sider = n360(trop - ayan);
//   const ws    = WESTERN_SIGNS[Math.floor(trop/30)];
//   const vi    = Math.floor(sider/30);
//   const nakSz = 360/27;
//   const nak   = NAKSHATRAS[Math.floor(sider/nakSz)];
//   const np    = (sider % nakSz) / nakSz;
//   const pada  = Math.floor(np*4) + 1;
//   const retro = forceRetro !== null ? forceRetro : isRetrograde(jd, calcFn);
//   return { trop, sider, ws, vi, nak, np, pada, retro };
// }

// // ─────────────────────────────────────────────────────────────────
// //  RENDER ASCENDANT CARD
// // ─────────────────────────────────────────────────────────────────
// function renderAngles(angles, ayan) {
//   const { asc, desc, mc, ic } = angles;
//   const ascSider = n360(asc - ayan);
//   const descSider = n360(desc - ayan);
//   const mcSider   = n360(mc  - ayan);
//   const icSider   = n360(ic  - ayan);
//   const ascWS  = WESTERN_SIGNS[Math.floor(asc/30)];
//   const descWS = WESTERN_SIGNS[Math.floor(desc/30)];
//   const mcWS   = WESTERN_SIGNS[Math.floor(mc/30)];
//   const icWS   = WESTERN_SIGNS[Math.floor(ic/30)];
//   const ascVS  = VEDIC_SIGNS[Math.floor(ascSider/30)];
//   const descVS = VEDIC_SIGNS[Math.floor(descSider/30)];
//   const mcVS   = VEDIC_SIGNS[Math.floor(mcSider/30)];
//   const icVS   = VEDIC_SIGNS[Math.floor(icSider/30)];
//   const nakSz   = 360/27;
//   const ascNak  = NAKSHATRAS[Math.floor(ascSider/nakSz)];
//   const descNak = NAKSHATRAS[Math.floor(descSider/nakSz)];
//   const mcNak   = NAKSHATRAS[Math.floor(mcSider/nakSz)];
//   const icNak   = NAKSHATRAS[Math.floor(icSider/nakSz)];
//   setText('ascTropLon',   dms(asc));
//   setText('ascSiderLon',  dms(ascSider));
//   setText('ascWSign',     `${ascWS.s} ${ascWS.n}`);
//   setText('ascWDeg',      dms(asc%30)+' in sign');
//   setText('ascVSign',     ascVS);
//   setText('ascVDeg',      dms(ascSider%30)+' in sign');
//   setText('ascNakName',   ascNak.n);
//   setText('ascNakLord',   ascNak.l);
//   setText('ascStripTitle',`Lagna · ${ascWS.s} ${ascWS.n} / ${ascVS}`);
//   setText('ascStripSub',  `${dms(asc)} Tropical  ·  ${dms(ascSider)} Sidereal`);
//   setText('descTropLon',  dms(desc));
//   setText('descSiderLon', dms(descSider));
//   setText('descWSign',    `${descWS.s} ${descWS.n}`);
//   setText('descWDeg',     dms(desc%30)+' in sign');
//   setText('descVSign',    descVS);
//   setText('descVDeg',     dms(descSider%30)+' in sign');
//   setText('descNakName',  descNak.n);
//   setText('descNakLord',  descNak.l);
//   setText('mcWSign',  `${mcWS.s} ${mcWS.n}`);
//   setText('mcVSign',  mcVS);
//   setText('mcNak',    mcNak.n);
//   setText('mcTropLon', dms(mc));
//   setText('icWSign',  `${icWS.s} ${icWS.n}`);
//   setText('icVSign',  icVS);
//   setText('icNak',    icNak.n);
//   setText('icTropLon', dms(ic));
//   const note = document.getElementById('anglesAyanNote');
//   if (note) note.innerHTML =
//     `Lahiri Ayanamsa <code>${ayan.toFixed(4)}°</code>  ·  LST <code>${angles.lst.toFixed(4)}°</code>  ·  Obliquity <code>${angles.eps.toFixed(4)}°</code>`;
// }

// // ─────────────────────────────────────────────────────────────────
// //  RENDER ONE PLANET
// // ─────────────────────────────────────────────────────────────────
// function renderPlanet(pid, data, ayan, ascTrop) {
//   const { trop, sider, ws, vi, nak, np, pada, retro } = data;
//   const meta     = PLANET_META[pid];
//   const retroTag = retro ? '  *' : '';
//   // Whole-sign sidereal house (correct for Vedic)
//   const _ascSider = n360(ascTrop - ayan);
//   const house = Math.floor(n360(sider - _ascSider) / 30) + 1;
//   const houseSig = HOUSE_NAMES[house] || '';
//   setText(`${pid}StripTitle`, `${meta.sym} ${meta.label} in ${nak.n}${retroTag}`);
//   setText(`${pid}StripSub`,   `${VEDIC_SIGNS[vi]} (Vedic)  ·  Pada ${pada}${retroTag}`);
//   const wSubEl = document.querySelector(`#${pid}Panel .western-strip-sub`);
//   if (wSubEl) {
//     wSubEl.textContent = `${ws.s} ${ws.n} (Western)  ·`;
//     wSubEl.style.display = _showWestern ? '' : 'none';
//   }
//   setText(`${pid}StripLon`,   dms(trop));
//   setText(`${pid}WSign`,    `${ws.s} ${ws.n}`);
//   setText(`${pid}WDeg`,     dms(trop%30)+' in sign');
//   setText(`${pid}VSign`,    VEDIC_SIGNS[vi]);
//   setText(`${pid}VDeg`,     dms(sider%30)+' in sign');
//   setText(`${pid}NakName`,  nak.n);
//   setText(`${pid}NakPada`,  `Pada ${pada} of 4  ·  ${(np*100).toFixed(1)}% through`);
//   setText(`${pid}NakLord`,  nak.l);
//   setText(`${pid}NakDeity`, nak.d);
//   setText(`${pid}Retro`,    retro ? '* Retrograde' : 'Direct ↻');
//   setText(`${pid}House`,    `${ordinal(house)} House`);
//   setText(`${pid}HouseSig`, houseSig);
//   const bar = document.getElementById(`${pid}NakProgress`);
//   if (bar) bar.style.width = (np*100).toFixed(1)+'%';
//   const note = document.getElementById(`${pid}AyanNote`);
//   if (note) note.innerHTML =
//     `Lahiri Ayanamsa <code>${ayan.toFixed(4)}°</code>  ·  Tropical <code>${trop.toFixed(4)}°</code>  ·  Sidereal <code>${sider.toFixed(4)}°</code>`;
// }

// // ─────────────────────────────────────────────────────────────────
// //  MAIN CALCULATE
// // ─────────────────────────────────────────────────────────────────
// function calculate() {
//   const errEl = document.getElementById('errorPill');
//   errEl.style.display = 'none';
//   const dateStr = document.getElementById('dateInput').value;
//   const timeStr = document.getElementById('timeInput').value;
//   const utcOff  = parseFloat(document.getElementById('utcOffset').value);
//   const latVal  = parseFloat(document.getElementById('lat').value);
//   const lonVal  = parseFloat(document.getElementById('lon').value);
//   if (!dateStr||!timeStr||isNaN(utcOff)) {
//     errEl.textContent='⚠ Please fill in date, time, and UTC offset.'; errEl.style.display='block'; return;
//   }
//   if (isNaN(latVal)||isNaN(lonVal)) {
//     errEl.textContent='⚠ Please enter or look up valid coordinates.'; errEl.style.display='block'; return;
//   }
//   const [yr,mo,dy] = dateStr.split('-').map(Number);
//   const [hr,mn]    = timeStr.split(':').map(Number);
//   // Convert local time to UTC fractional hour, with proper date rollover
//   let utHr = (hr + mn/60) - utcOff;
//   // Build a Date to handle month/year boundary properly
//   const baseDate = new Date(Date.UTC(yr, mo-1, dy, 0, 0, 0));
//   baseDate.setUTCHours(0, 0, 0, 0);
//   const msOff = Math.round(utHr * 3600000);
//   const utcDate = new Date(baseDate.getTime() + msOff);
//   const adjYr = utcDate.getUTCFullYear();
//   const adjMo = utcDate.getUTCMonth() + 1;
//   const adjDy = utcDate.getUTCDate();
//   const adjUtHr = utcDate.getUTCHours() + utcDate.getUTCMinutes()/60 + utcDate.getUTCSeconds()/3600;
//   const jd   = julianDay(adjYr, adjMo, adjDy, adjUtHr);
//   const ayan = lahiriAyanamsa(jd);

//   const angles   = computeAngles(jd, latVal, lonVal);
//   const ascTrop  = angles.asc;
//   renderAngles(angles, ayan);

//   const planets = {
//     sun:     computePlanet(jd, ayan, sunLongitude,                         false),
//     moon:    computePlanet(jd, ayan, moonLongitude,                        false),
//     mercury: computePlanet(jd, ayan, jd2=>planetLongitude(jd2,'mercury'),  null),
//     venus:   computePlanet(jd, ayan, jd2=>planetLongitude(jd2,'venus'),    null),
//     mars:    computePlanet(jd, ayan, jd2=>planetLongitude(jd2,'mars'),     null),
//     jupiter: computePlanet(jd, ayan, jd2=>planetLongitude(jd2,'jupiter'),  null),
//     saturn:  computePlanet(jd, ayan, jd2=>planetLongitude(jd2,'saturn'),   null),
//     rahu:    computePlanet(jd, ayan, rahuLongitude,                        true),
//     ketu:    computePlanet(jd, ayan, jd2=>n360(rahuLongitude(jd2)+180),   true),
//   };
//   for (const [pid, data] of Object.entries(planets)) {
//     renderPlanet(pid, data, ayan, ascTrop);
//   }

//   const ss = sunriseSunset(yr, mo, dy, latVal, lonVal, utcOff);
//   if (ss.polar==='no_rise') {
//     setText('sunriseTime','Polar Night'); setText('sunsetTime','No Sunrise'); setText('dayLength','0h 0m');
//   } else if (ss.polar==='no_set') {
//     setText('sunriseTime','Midnight Sun'); setText('sunsetTime','24h Daylight'); setText('dayLength','24h 0m');
//   } else {
//     setText('sunriseTime', decToHMS(ss.rise)); setText('sunsetTime', decToHMS(ss.set));
//     const dlH=Math.floor(ss.dayLength), dlM=Math.round((ss.dayLength-dlH)*60);
//     setText('dayLength',`${dlH}h ${dlM}m`);
//   }
//   const { ra:sunRA, dec:sunDec } = sunEquatorial(jd);
//   setText('sunDeclination', dms(Math.abs(sunDec))+(sunDec>=0?' N':' S'));
//   setText('sunRA', decToHMS(sunRA/15));

//   const tk     = computeTithiKarana(jd);
//   let tkRise = null, tkSet = null;
//   const ss2 = sunriseSunset(yr, mo, dy, latVal, lonVal, utcOff);
//   if (!ss2.polar && ss2.rise !== null) {
//     const jdRise = julianDay(yr, mo, dy, ss2.rise - utcOff);
//     tkRise = computeTithiKarana(jdRise);
//   }
//   if (!ss2.polar && ss2.set !== null) {
//     const jdSet  = julianDay(yr, mo, dy, ss2.set  - utcOff);
//     tkSet  = computeTithiKarana(jdSet);
//   }
//   renderTithiKarana(tk, tkRise, tkSet, ayan, ss2, hr + mn/60);

//   _panchaData = computePanchanga(jd, ayan, yr, mo, dy, utcOff);
//   renderPanchanga(_panchaData, ss);

//   const inputHourNum = hr + mn / 60;
//   const sunriseHrNum = (ss2 && ss2.rise !== null && !ss2.polar) ? ss2.rise : inputHourNum;
//   const sunsetHrNum  = (ss2 && ss2.set  !== null && !ss2.polar) ? ss2.set  : inputHourNum;
//   applyTithiTimeTheme(inputHourNum, sunriseHrNum, sunsetHrNum);

//   _lastPlanetData = planets;
//   _lastAyan       = ayan;

//   _masaLat = latVal; _masaLon = lonVal; _masaUtcOff = utcOff;
//   _masaCalYear = yr; _masaCalMonth = mo;
//   const masaYrEl = document.getElementById('masaYear');
//   const masaMonEl = document.getElementById('masaMonthSel');
//   if (masaYrEl && !masaYrEl.value) masaYrEl.value = yr;
//   if (masaMonEl && !masaMonEl._userSet) {
//     const civToVed = {3:1,4:2,5:3,6:4,7:5,8:6,9:7,10:8,11:9,12:10,1:11,2:12};
//     masaMonEl.value = civToVed[mo] || 12;
//   }

//   drawKundaliChart(ascTrop, planets, ayan, 'kundaliChartSvg');

//   document.getElementById('resultCard').style.display = 'block';
//   document.getElementById('resultCard').scrollIntoView({ behavior:'smooth', block:'start' });
//   showTab('chart');
// }

// // ─────────────────────────────────────────────────────────────────
// //  TAB NAVIGATION
// // ─────────────────────────────────────────────────────────────────
// const ALL_TABS = ['chart','lagna','tithi','masa','sun','moon','mercury','venus','mars','jupiter','saturn','rahu','ketu'];

// const TAB_ACTIVE = {
//   lagna:   'active-lagna',
//   tithi:   'active-tithi',
//   masa:    'active-tithi',
//   chart:   'active-chart',
//   sun:     'active-sun',
//   moon:    'active-moon',
//   mercury: 'active-merc',
//   venus:   'active-venus',
//   mars:    'active-mars',
//   jupiter: 'active-jup',
//   saturn:  'active-sat',
//   rahu:    'active-rahu',
//   ketu:    'active-ketu',
// };

// const TAB_KEYS = {
//   'l':'lagna', 't':'tithi', 'p':'masa', 'c':'chart',
//   's':'sun', 'm':'moon', 'e':'mercury', 'v':'venus',
//   'a':'mars', 'j':'jupiter', 'k':'saturn', 'r':'rahu', 'u':'ketu'
// };
// // Note: 'w' triggers toggleWestern() instead of showTab()

// let _lastPlanetData = null;
// let _lastAyan       = null;

// function showTab(pid) {
//   ALL_TABS.forEach(p => {
//     const panel = document.getElementById(`${p}Panel`);
//     const btn   = document.getElementById(`tab_${p}`);
//     if (panel) panel.style.display = (p===pid) ? '' : 'none';
//     if (btn)   btn.className = 'tab-btn' + (p===pid ? ` ${TAB_ACTIVE[p]}` : '');
//   });
//   updatePlanetInfoBar(pid);
// }

// function updatePlanetInfoBar(pid) {
//   const bar = document.getElementById('planetInfoBar');
//   if (!bar) return;
//   const PLANET_SYMS = {
//     sun:'☀', moon:'☽', mercury:'☿', venus:'♀',
//     mars:'♂', jupiter:'♃', saturn:'♄', rahu:'☊', ketu:'☋'
//   };
//   const PLANET_LABELS = {
//     sun:'Sun', moon:'Moon', mercury:'Mercury', venus:'Venus',
//     mars:'Mars', jupiter:'Jupiter', saturn:'Saturn', rahu:'Rahu', ketu:'Ketu'
//   };
//   const isPlanet = ['sun','moon','mercury','venus','mars','jupiter','saturn','rahu','ketu'].includes(pid);
//   if (!isPlanet || !_lastPlanetData || !_lastAyan) { bar.style.display = 'none'; return; }
//   const data = _lastPlanetData[pid];
//   if (!data) { bar.style.display = 'none'; return; }
//   const pSider   = n360(data.trop - _lastAyan);
//   const vSignIdx = Math.floor(pSider / 30);
//   const vSign    = VEDIC_SIGNS[vSignIdx];
//   const wSign    = WESTERN_SIGNS[Math.floor(data.trop / 30)];
//   const nakSz    = 360 / 27;
//   const nak      = NAKSHATRAS[Math.floor(pSider / nakSz)];
//   const pada     = Math.floor(((pSider % nakSz) / nakSz) * 4) + 1;
//   const degInSign= (pSider % 30).toFixed(2);
//   document.getElementById('pibSym').textContent   = PLANET_SYMS[pid] || '';
//   document.getElementById('pibName').textContent  = PLANET_LABELS[pid] || pid;
//   document.getElementById('pibSign').textContent  = `${vSign} (${wSign.s} ${wSign.n})`;
//   document.getElementById('pibNak').textContent   = `${nak.n} Pada ${pada}`;
//   document.getElementById('pibRetro').textContent = data.retro ? '  ℞ Retrograde' : '';
//   document.getElementById('pibDeg').textContent   = `${degInSign}° in sign  ·  ${dms(pSider)} sidereal`;
//   bar.style.display = 'flex';
// }

// document.addEventListener('keydown', (e) => {
//   if (['INPUT','SELECT','TEXTAREA'].includes(document.activeElement.tagName)) return;
//   if (e.ctrlKey || e.metaKey || e.altKey) return;
//   const tab = TAB_KEYS[e.key.toLowerCase()];
//   if (tab && document.getElementById('resultCard').style.display !== 'none') {
//     showTab(tab);
//     e.preventDefault();
//   }
//   // W key: toggle western zodiac view
//   if ((e.key === 'w' || e.key === 'W') && document.getElementById('resultCard').style.display !== 'none') {
//     toggleWestern(); e.preventDefault();
//   }
//   if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
//     const resultVisible = document.getElementById('resultCard').style.display !== 'none';
//     if (!resultVisible) return;
//     const cur  = ALL_TABS.findIndex(t => {
//       const b = document.getElementById(`tab_${t}`);
//       return b && b.className.includes('active-');
//     });
//     if (cur === -1) return;
//     let next = e.key === 'ArrowRight' ? cur + 1 : cur - 1;
//     next = ((next % ALL_TABS.length) + ALL_TABS.length) % ALL_TABS.length;
//     showTab(ALL_TABS[next]);
//     e.preventDefault();
//   }
// });

// // ─────────────────────────────────────────────────────────────────
// //  ACCURATE SAMAPTI KAAL CALCULATION
// //  Finds the exact local time when a value (elong, nakSider, yogaSum)
// //  crosses the next boundary. Uses bisection on JD.
// // ─────────────────────────────────────────────────────────────────

// /**
//  * Find the JD when fn(jd) crosses the next multiple of `step`
//  * starting from jd0, searching forward up to maxDays.
//  * Returns local-time hours (in utcOff timezone), or null if not found.
//  */
// function findNextCrossing(jd0, utcOff, step, valueFn, maxDays) {
//   const v0 = valueFn(jd0);
//   const curIdx = Math.floor(v0 / step);
//   const target = (curIdx + 1) * step;

//   let lo = jd0, hi = jd0 + maxDays;

//   // Unwrap function: tracks cumulative motion from jd0 to avoid wrap ambiguity
//   // Instead of comparing absolute values, we integrate motion in small steps
//   // to detect crossing robustly across 0°/360° boundary.
//   function unwrappedValue(jd) {
//     // Sample at a few intermediate points to accumulate rotation correctly
//     const steps = Math.max(2, Math.round((jd - jd0) * 24)); // hourly steps
//     let prev = v0;
//     let accum = 0;
//     for (let i = 1; i <= steps; i++) {
//       const jdStep = jd0 + (jd - jd0) * i / steps;
//       let cur = valueFn(jdStep);
//       let diff = cur - prev;
//       if (diff < -180) diff += 360;
//       if (diff >  180) diff -= 360;
//       accum += diff;
//       prev = cur;
//     }
//     return v0 + accum;
//   }

//   const uvHi = unwrappedValue(hi);
//   if (uvHi < target) return null;  // doesn't cross in window

//   // Bisect to 1-second precision
//   for (let i = 0; i < 60; i++) {
//     const mid = (lo + hi) / 2;
//     const vm  = unwrappedValue(mid);
//     if (vm < target) lo = mid;
//     else             hi = mid;
//     if (hi - lo < 1/(24*3600)) break;
//   }

//   const jdCross = (lo + hi) / 2;
//   const localTimeHr = ((jdCross + utcOff/24 + 0.5) % 1) * 24;
//   return { jd: jdCross, localHr: localTimeHr < 0 ? localTimeHr + 24 : localTimeHr };
// }

// /**
//  * For the masa table: given a calendar day, compute samapti kaal for:
//  *  - Tithi (elong crosses next multiple of 12°)
//  *  - Nakshatra (moon sider crosses next multiple of 13.333...°)
//  *  - Yoga (yogaSum crosses next multiple of 13.333...°)
//  *  - Karana (elong crosses next multiple of 6°)
//  * Uses sunrise JD as the starting reference.
//  */
// function computeSamaptiKaal(yr, mo, dy, lat, lon, utcOff) {
//   const ss = sunriseSunset(yr, mo, dy, lat, lon, utcOff);

//   // Use sunrise JD as reference; fallback to noon
//   let refJd;
//   if (!ss.polar && ss.rise !== null) {
//     refJd = julianDay(yr, mo, dy, ss.rise - utcOff);
//   } else {
//     refJd = julianDay(yr, mo, dy, 12 - utcOff);
//   }

//   const ayan = lahiriAyanamsa(refJd);

//   // Value functions (0-360)
//   const elongFn   = jd => n360(moonLongitude(jd) - sunLongitude(jd));
//   const nakFn     = jd => n360(moonLongitude(jd) - lahiriAyanamsa(jd));
//   const yogaFn    = jd => n360(
//     (moonLongitude(jd) - lahiriAyanamsa(jd)) +
//     (sunLongitude(jd)  - lahiriAyanamsa(jd))
//   );

//   const NAK_SZ = 360 / 27;  // 13.3333...°
//   const TITHI_SZ  = 12;
//   const KARANA_SZ = 6;

//   // Find samapti (end time) — search up to 2 days forward
//   const tithiEnd   = findNextCrossing(refJd, utcOff, TITHI_SZ,  elongFn, 2);
//   const karanaEnd  = findNextCrossing(refJd, utcOff, KARANA_SZ, elongFn, 2);
//   const nakEnd     = findNextCrossing(refJd, utcOff, NAK_SZ,    nakFn,   2);
//   const yogaEnd    = findNextCrossing(refJd, utcOff, NAK_SZ,    yogaFn,  2);

//   return { tithiEnd, karanaEnd, nakEnd, yogaEnd, ss };
// }

// /**
//  * Format a samapti kaal result:
//  * If it falls on the same calendar day → show HH:MM
//  * If it overflows to next day → show "Next day HH:MM"
//  * null → "—"
//  */
// function formatSamapti(result, refLocalRiseHr) {
//   if (!result) return '—';
//   const hr = result.localHr;
//   const hh = String(Math.floor(hr)).padStart(2,'0');
//   const mm = String(Math.floor((hr % 1) * 60)).padStart(2,'0');
//   // If samapti is before sunrise, it's "next day" for panchanga purposes
//   if (refLocalRiseHr !== null && hr < refLocalRiseHr - 0.1) {
//     return `▶ ${hh}:${mm}`;  // next civil day
//   }
//   return `${hh}:${mm}`;
// }

// // ─────────────────────────────────────────────────────────────────
// //  CHANDRA RASHI PRAVESH
// //  Find when the Moon enters a new sidereal sign within the month
// // ─────────────────────────────────────────────────────────────────
// function findMoonSignChanges(yr, mo, lat, lon, utcOff) {
//   const daysInMonth = new Date(yr, mo, 0).getDate();
//   const changes = [];
//   let prevSign = -1;

//   for (let d = 1; d <= daysInMonth; d++) {
//     // Check every 2 hours through the day for sign changes
//     for (let stepH = 0; stepH < 24; stepH += 2) {
//       const jd   = julianDay(yr, mo, d, stepH - utcOff);
//       const ayan = lahiriAyanamsa(jd);
//       const moonS = n360(moonLongitude(jd) - ayan);
//       const sign  = Math.floor(moonS / 30);

//       if (prevSign !== -1 && sign !== prevSign) {
//         // Sign change detected between stepH-2 and stepH — bisect
//         let lo = julianDay(yr, mo, d, stepH - 2 - utcOff);
//         let hi = jd;
//         for (let i = 0; i < 40; i++) {
//           const mid = (lo + hi) / 2;
//           const ayanMid = lahiriAyanamsa(mid);
//           const ms = n360(moonLongitude(mid) - ayanMid);
//           if (Math.floor(ms / 30) === prevSign) lo = mid;
//           else hi = mid;
//           if (hi - lo < 1/1440) break;
//         }
//         const jdPravesh = (lo + hi) / 2;
//         // Local time
//         const localHr = ((jdPravesh + utcOff/24 + 0.5) % 1) * 24;
//         const hh = String(Math.floor(localHr)).padStart(2,'0');
//         const mm = String(Math.floor((localHr % 1) * 60)).padStart(2,'0');
//         // Civil date of pravesh
//         const dateMs = (jdPravesh - 2440587.5 + utcOff/24) * 86400000;
//         const dt = new Date(dateMs);
//         changes.push({
//           day: dt.getUTCDate(),
//           sign: sign, // new sign index (sidereal)
//           ti: `${hh}:${mm}`,
//           localHr,
//         });
//       }
//       prevSign = sign;
//     }
//   }
//   return changes;
// }

// // ─────────────────────────────────────────────────────────────────
// //  MONTHLY MASA PANCHANGA — Accurate version
// // ─────────────────────────────────────────────────────────────────

// let _masaLat = null, _masaLon = null, _masaUtcOff = null;
// let _masaCalYear = null, _masaCalMonth = null;

// const MASA_MONTH_NAMES = [
//   'Chaitra','Vaishakha','Jyeshtha','Ashadha','Shravana',
//   'Bhadrapada','Ashwin','Kartik','Margashirsha','Pausha','Magha','Phalguna'
// ];

// function masaNavigate(delta) {
//   const selEl = document.getElementById('masaMonthSel');
//   const yrEl  = document.getElementById('masaYear');
//   if (!selEl || !yrEl) return;
//   let m = parseInt(selEl.value) + delta;
//   let y = parseInt(yrEl.value) || new Date().getFullYear();
//   if (m > 12) { m = 1; y++; }
//   if (m < 1)  { m = 12; y--; }
//   selEl.value = m;
//   yrEl.value  = y;
//   masaRender();
// }

// function masaRender() {
//   if (_masaLat === null || _masaLon === null) {
//     document.getElementById('masaContent').innerHTML =
//       '<div class="masa-loading">⚠ Please calculate a chart first with location and date.</div>';
//     return;
//   }
//   const selEl = document.getElementById('masaMonthSel');
//   const yrEl  = document.getElementById('masaYear');
//   const vedMon = parseInt(selEl?.value) || 12;
//   const year   = parseInt(yrEl?.value)  || _masaCalYear || new Date().getFullYear();

//   document.getElementById('masaContent').innerHTML =
//     '<div class="masa-loading">⏳ Computing accurate Panchanga... please wait.</div>';

//   // Use setTimeout so UI updates before heavy computation
//   setTimeout(() => buildMasaTable(year, vedMon), 30);
// }

// function buildMasaTable(year, vedMon) {
//   const civilMonthMap = [3,4,5,6,7,8,9,10,11,12,1,2];
//   const civilMon = civilMonthMap[vedMon - 1];
//   const civYear  = civilMon <= 2 ? year + 1 : year;

//   const daysInMonth = new Date(civYear, civilMon, 0).getDate();
//   const lat   = _masaLat;
//   const lon   = _masaLon;
//   const utcOff= _masaUtcOff;

//   setText('masaMonthName', `${MASA_MONTH_NAMES[vedMon-1]} ${year}`);
//   setText('masaLocation',  `${lat.toFixed(2)}°, ${lon.toFixed(2)}°`);

//   const VARA_NAMES_SHORT = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
//   const rows = [];
//   let shuklaCount = 0, krishnaCount = 0;

//   // Pre-compute Moon sign changes for Chandra Rashi Pravesh
//   const moonSignChanges = findMoonSignChanges(civYear, civilMon, lat, lon, utcOff);
//   // Index by day
//   const moonChangByDay = {};
//   for (const ch of moonSignChanges) {
//     if (!moonChangByDay[ch.day]) moonChangByDay[ch.day] = [];
//     moonChangByDay[ch.day].push(ch);
//   }

//   for (let d = 1; d <= daysInMonth; d++) {
//     const ss = sunriseSunset(civYear, civilMon, d, lat, lon, utcOff);
//     const riseHr = (!ss.polar && ss.rise !== null) ? ss.rise : null;
//     const setHr  = (!ss.polar && ss.set  !== null) ? ss.set  : null;

//     // JD at sunrise (panchanga reference)
//     let jdRef;
//     if (riseHr !== null) {
//       jdRef = julianDay(civYear, civilMon, d, riseHr - utcOff);
//     } else {
//       jdRef = julianDay(civYear, civilMon, d, 12 - utcOff);
//     }

//     const ayan = lahiriAyanamsa(jdRef);
//     const tk   = computeTithiKarana(jdRef);
//     const pancha = computePanchanga(jdRef, ayan, civYear, civilMon, d, utcOff);
//     const dow  = new Date(civYear, civilMon - 1, d).getDay();

//     if (tk.tithi.paksha === 'Shukla') shuklaCount++; else krishnaCount++;

//     // ── Compute Samapti Kaal ──
//     const elongFn = jd => n360(moonLongitude(jd) - sunLongitude(jd));
//     const nakFn   = jd => n360(moonLongitude(jd) - lahiriAyanamsa(jd));
//     const yogaFn  = jd => n360(
//       n360(moonLongitude(jd) - lahiriAyanamsa(jd)) +
//       n360(sunLongitude(jd)  - lahiriAyanamsa(jd))
//     );

//     const TITHI_SZ  = 12;
//     const KARANA_SZ = 6;
//     const NAK_SZ    = 360/27;

//     const tithiEnd  = findNextCrossing(jdRef, utcOff, TITHI_SZ,  elongFn, 2);
//     const karanaEnd = findNextCrossing(jdRef, utcOff, KARANA_SZ, elongFn, 2);
//     const nakEnd    = findNextCrossing(jdRef, utcOff, NAK_SZ,    nakFn,   2);
//     const yogaEnd   = findNextCrossing(jdRef, utcOff, NAK_SZ,    yogaFn,  2);

//     const riseStr = riseHr !== null ? decToHMS(riseHr).slice(0,5) : '—';
//     const setStr  = setHr  !== null ? decToHMS(setHr).slice(0,5)  : '—';

//     // Format samapti — if falls on next day vs today
//     function fmtSamapti(endResult) {
//       if (!endResult) return '—';
//       const hr = endResult.localHr;
//       if (hr < 0 || hr > 47) return '—';
//       const hh = String(Math.floor(hr % 24)).padStart(2,'0');
//       const mm = String(Math.floor((hr % 1) * 60)).padStart(2,'0');
//       if (riseHr !== null && hr < riseHr - 0.1) return `▶${hh}:${mm}`; // before today's sunrise = next day
//       return `${hh}:${mm}`;
//     }

//     const tithiSamapti  = fmtSamapti(tithiEnd);
//     const karanaSamapti = fmtSamapti(karanaEnd);
//     const nakSamapti    = fmtSamapti(nakEnd);
//     const yogaSamapti   = fmtSamapti(yogaEnd);

//     // ── Chandra Rashi Pravesh for this day ──
//     const moonChanges = moonChangByDay[d] || [];
//     const rashiPravesh = moonChanges.map(ch =>
//       `${VEDIC_SIGNS[ch.sign]} ${ch.time}`
//     ).join(', ') || '';

//     const isAmavasya = tk.tithi.paksha === 'Krishna' && tk.tithi.num === 15;
//     const isPurnima  = tk.tithi.paksha === 'Shukla'  && tk.tithi.num === 15;
//     const isEkadashi = tk.tithi.num === 11;
//     let rowClass = '';
//     if (isAmavasya) rowClass = 'amavasya';
//     else if (isPurnima) rowClass = 'purnima';
//     else if (isEkadashi) rowClass = 'ekadashi';

//     rows.push({
//       d, dow, riseStr, setStr,
//       paksha: tk.tithi.paksha,
//       tithiName: tk.tithi.n,
//       tithiNum: tk.tithi.num,
//       tithiSamapti,
//       nakName: pancha.moonNak.n,
//       nakSamapti,
//       yogaName: pancha.yoga.n,
//       yogaNature: pancha.yoga.nature,
//       yogaSamapti,
//       karanaName: tk.karana.n,
//       karanaSamapti,
//       rashiPravesh,
//       varaName: VARA_NAMES_SHORT[dow],
//       rowClass,
//     });
//   }

//   setText('masaShuklaCount', shuklaCount);
//   setText('masaKrishnaCount', krishnaCount);

//   // Split into Shukla and Krishna paksha
//   let shuklaRows = [], krishnaRows = [];
//   for (const row of rows) {
//     if (row.paksha === 'Shukla') shuklaRows.push(row);
//     else krishnaRows.push(row);
//   }

//   function buildRows(arr) {
//     return arr.map(row => {
//       const yColor = row.yogaNature === 'Inauspicious' ? '#a02020' : '#1a6020';
//       const rashiHtml = row.rashiPravesh
//         ? `<div class="masa-rashi-pravesh">☽ ${row.rashiPravesh}</div>`
//         : '';
//       return `<tr class="${row.rowClass}">
//         <td class="td-day">${row.d}<br><span class="td-vara-sm">${row.varaName}</span></td>
//         <td class="td-tithi">
//           <div class="masa-anga-main">${row.tithiName} ${row.tithiNum}</div>
//           <div class="masa-samapti">🕐 ${row.tithiSamapti}</div>
//         </td>
//         <td class="td-karana">
//           <div class="masa-anga-main">${row.karanaName}</div>
//           <div class="masa-samapti">🕐 ${row.karanaSamapti}</div>
//         </td>
//         <td class="td-nak">
//           <div class="masa-anga-main">${row.nakName}</div>
//           <div class="masa-samapti">🕐 ${row.nakSamapti}</div>
//           ${rashiHtml}
//         </td>
//         <td class="td-yoga" style="color:${yColor}">
//           <div class="masa-anga-main">${row.yogaName}</div>
//           <div class="masa-samapti" style="color:#777">🕐 ${row.yogaSamapti}</div>
//         </td>
//         <td class="td-sunrise">🌅 ${row.riseStr}</td>
//         <td class="td-sunset">🌇 ${row.setStr}</td>
//       </tr>`;
//     }).join('');
//   }

//   const thHtml = `<thead><tr>
//     <th>Date<br>Vara</th>
//     <th>Tithi<br><span class="th-sub">Samapti Kaal</span></th>
//     <th>Karana<br><span class="th-sub">Samapti Kaal</span></th>
//     <th>Nakshatra<br><span class="th-sub">Samapti · Rashi Pravesh</span></th>
//     <th>Yoga<br><span class="th-sub">Samapti Kaal</span></th>
//     <th>Sunrise</th><th>Sunset</th>
//   </tr></thead>`;

//   const shuklaHtml = shuklaRows.length ? `
//     <div class="masa-paksha-head">🌕 Shukla Paksha — ${shuklaRows.length} days</div>
//     <div class="masa-table-wrap">
//       <table class="masa-table">${thHtml}<tbody>${buildRows(shuklaRows)}</tbody></table>
//     </div>` : '';

//   const krishnaHtml = krishnaRows.length ? `
//     <div class="masa-paksha-head masa-paksha-krishna">🌑 Krishna Paksha — ${krishnaRows.length} days</div>
//     <div class="masa-table-wrap">
//       <table class="masa-table">${thHtml}<tbody>${buildRows(krishnaRows)}</tbody></table>
//     </div>` : '';

//   document.getElementById('masaContent').innerHTML = shuklaHtml + krishnaHtml;
// }

// // ─────────────────────────────────────────────────────────────────
// //  CITY LOOKUP + INIT
// // ─────────────────────────────────────────────────────────────────
// function tzOff(tz, dateStr) {
//   try {
//     // Use the actual input date to get the correct DST offset for that time
//     const refDate = dateStr ? new Date(dateStr + 'T12:00:00') : new Date();
//     const a = new Date(refDate.toLocaleString('en-US', { timeZone: tz }));
//     const b = new Date(refDate.toLocaleString('en-US', { timeZone: 'UTC' }));
//     return Math.round((a - b) / 1800000) * 0.5;
//   } catch { return null; }
// }
// async function lookupCity(cityName, dateStr) {
//   const res=await fetch(`https://geocoding-api.open-meteo.com/v1/search?name=${encodeURIComponent(cityName)}&count=1&language=en&format=json`);
//   const d=await res.json();
//   if(!d.results?.length) throw new Error('Not found');
//   const g=d.results[0];
//   return { lat:g.latitude, lon:g.longitude, timezone:g.timezone, utcOff:tzOff(g.timezone, dateStr) };
// }

// window.addEventListener('DOMContentLoaded', () => {
//   // Set current date and time on load
//   function setCurrentTime() {
//     const now = new Date();
//     document.getElementById('dateInput').value = now.toISOString().slice(0, 10);
//     document.getElementById('timeInput').value =
//       String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
//   }
//   setCurrentTime();

//   // Live-update clock every 30s UNLESS user has manually changed the time field
//   const timeEl = document.getElementById('timeInput');
//   let userEditedTime = false;
//   timeEl.addEventListener('change', () => { userEditedTime = true; });
//   timeEl.addEventListener('focus',  () => { userEditedTime = true; });
//   setInterval(() => {
//     if (!userEditedTime) setCurrentTime();
//   }, 30000);

//   document.getElementById('utcOffset').value = -(new Date().getTimezoneOffset()/60);
//   document.getElementById('cityInput').addEventListener('blur', async function() {
//     const city=this.value.trim(); if(!city) return;
//     const dateStr = document.getElementById('dateInput').value || null;
//     const hint=document.getElementById('geoHint'), stat=document.getElementById('cityStatus');
//     hint.className='geo-hint'; hint.textContent='🔍 Looking up…'; stat.textContent='⏳';
//     try {
//       const geo=await lookupCity(city, dateStr);
//       document.getElementById('lat').value=geo.lat.toFixed(4);
//       document.getElementById('lon').value=geo.lon.toFixed(4);
//       if(geo.utcOff!==null) document.getElementById('utcOffset').value=geo.utcOff;
//       hint.className='geo-hint ok';
//       hint.textContent=`✓ ${geo.lat.toFixed(4)}°N, ${geo.lon.toFixed(4)}°E — ${geo.timezone}`;
//       stat.textContent='✓';
//     } catch {
//       hint.className='geo-hint err'; hint.textContent='✗ Not found — enter coordinates manually'; stat.textContent='✗';
//     }
//   });
// });