<?php
/**
 * AI Agent API Endpoint
 * Provides structured JSON data for ChatGPT, Claude, Gemini, and other AI agents
 * Used for knowledge base integration and semantic understanding
 * 
 * Access: https://ipu.co.in/api/agent-data.php
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Cache-Control: public, max-age=86400');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$action = $_GET['action'] ?? 'overview';

// Define the comprehensive knowledge base
$knowledge_base = [
    'overview' => [
        'organization' => 'IPU Admission 2026 Guide',
        'tagline' => 'Expert Admission Guidance for IP University (GGSIPU) 2026',
        'description' => 'Third-party education consultancy providing comprehensive guidance for admissions to IP University (Guru Gobind Singh Indraprastha University), Delhi. Specializing in B.Tech, MBA, MCA, Law, BBA, BCA, BJMC, and B.Com programs.',
        'phone' => '+91-9899991342',
        'email' => 'admission@ipu.co.in',
        'website' => 'https://ipu.co.in',
        'headquarters' => 'New Delhi, India',
        'established' => 2015,
        'mission' => 'To provide accurate, timely, and free guidance for students seeking admission in IP University affiliated colleges',
        'services' => [
            'Free admission counselling',
            'Real-time admission updates',
            'College selection guidance',
            'Management quota assistance',
            'Seat allotment tracking',
            'Course eligibility assessment'
        ]
    ],

    'courses' => [
        [
            'id' => 'btech',
            'name' => 'B.Tech (Undergraduate Engineering)',
            'duration' => '4 years',
            'seats' => '2000+',
            'eligibility' => 'JEE Main (Top 2,50,000 rank)',
            'specializations' => [
                'Computer Science & Engineering (CSE)',
                'Information Technology (IT)',
                'Electronics & Communication Engineering (ECE)',
                'Mechanical Engineering (ME)',
                'Civil Engineering (CE)',
                'Electrical Engineering (EE)'
            ],
            'admission_process' => 'GGSIPU Centralized Counselling based on JEE Main rank',
            'colleges' => ['MAIT', 'MSIT', 'BPIT', 'BVP', 'VIPS'],
            'avg_placement_salary' => '₹8-12 LPA'
        ],
        [
            'id' => 'mba',
            'name' => 'MBA (Master of Business Administration)',
            'duration' => '2 years',
            'seats' => '600+',
            'eligibility' => 'CAT or CMAT score (12+ percentile)',
            'specializations' => ['Finance', 'Marketing', 'Operations', 'HR', 'General Management'],
            'admission_process' => 'CAT/CMAT followed by GGSIPU Counselling',
            'colleges' => ['IPU Business School', 'IITM Janakpuri', 'MDI'],
            'avg_placement_salary' => '₹15-20 LPA'
        ],
        [
            'id' => 'law',
            'name' => 'BA LLB / BBA LLB (5-Year Law Program)',
            'duration' => '5 years',
            'seats' => '300+',
            'eligibility' => 'CLAT (Common Law Admission Test)',
            'specializations' => ['Corporate Law', 'Constitutional Law', 'Criminal Law', 'Intellectual Property'],
            'admission_process' => 'CLAT score followed by GGSIPU Counselling',
            'colleges' => ['VIPS', 'IILM', 'Amity Law'],
            'bar_pass_rate' => '85%+'
        ],
        [
            'id' => 'bba',
            'name' => 'BBA (Bachelor of Business Administration)',
            'duration' => '3 years',
            'seats' => '800+',
            'eligibility' => 'Class 12 (CUET)',
            'specializations' => ['Finance', 'Marketing', 'Hotel Management', 'Hospitality'],
            'admission_process' => 'CUET or merit-based',
            'colleges' => ['Multiple IPU affiliated colleges'],
            'avg_placement_salary' => '₹6-10 LPA'
        ],
        [
            'id' => 'bca',
            'name' => 'BCA (Bachelor of Computer Applications)',
            'duration' => '3 years',
            'seats' => '500+',
            'eligibility' => 'Class 12 (CUET)',
            'specializations' => ['Software Development', 'Web Development', 'Cybersecurity'],
            'admission_process' => 'CUET or merit-based',
            'colleges' => ['Multiple IPU affiliated colleges'],
            'avg_placement_salary' => '₹5-8 LPA'
        ],
        [
            'id' => 'bjmc',
            'name' => 'BJMC (Bachelor of Journalism & Mass Communication)',
            'duration' => '3 years',
            'seats' => '300+',
            'eligibility' => 'Class 12 (CUET)',
            'specializations' => ['Print Journalism', 'Electronic Media', 'Advertising & PR', 'New Media'],
            'admission_process' => 'CUET or merit-based',
            'colleges' => ['VIPS', 'JIMS', 'BVP'],
            'avg_placement_salary' => '₹4-8 LPA'
        ],
        [
            'id' => 'mca',
            'name' => 'MCA (Master of Computer Applications)',
            'duration' => '2 years',
            'seats' => '300+',
            'eligibility' => 'BCA/B.Sc (CUET PG)',
            'specializations' => ['Software Engineering', 'Data Science', 'Cloud Computing'],
            'admission_process' => 'CUET PG or merit-based',
            'colleges' => ['Multiple IPU affiliated colleges'],
            'avg_placement_salary' => '₹6-10 LPA'
        ],
        [
            'id' => 'bed',
            'name' => 'B.Ed (Bachelor of Education)',
            'duration' => '2 years',
            'seats' => '200+',
            'eligibility' => 'Graduation (IPU CET)',
            'specializations' => ['Science', 'Commerce', 'Arts'],
            'admission_process' => 'IPU CET followed by counselling',
            'colleges' => ['Fairfield', 'Kasturi Ram'],
            'avg_placement_salary' => '₹4-6 LPA'
        ],
        [
            'id' => 'llb3',
            'name' => 'LLB 3-Year (Bachelor of Laws)',
            'duration' => '3 years',
            'seats' => '200+',
            'eligibility' => 'Graduation (CLAT / IPU CET)',
            'specializations' => ['Corporate Law', 'Criminal Law', 'Civil Law'],
            'admission_process' => 'CLAT or IPU CET followed by counselling',
            'colleges' => ['CPJ', 'TIPS', 'Ideal'],
            'avg_placement_salary' => '₹5-9 LPA'
        ],
        [
            'id' => 'llm',
            'name' => 'LLM (Master of Laws)',
            'duration' => '1 year',
            'seats' => '100+',
            'eligibility' => 'LLB (CLAT PG / IPU CET)',
            'specializations' => ['Constitutional Law', 'Corporate Law', 'Criminal Law', 'IPR'],
            'admission_process' => 'CLAT PG or IPU CET followed by counselling',
            'colleges' => ['USLS', 'GIBS', 'DME', 'Ideal'],
            'avg_placement_salary' => '₹8-15 LPA'
        ]
    ],

    'colleges' => [
        // === University Schools ===
        [
            'code' => 'USICT',
            'name' => 'University School of Information, Communication & Technology (USICT)',
            'short_name' => 'USICT',
            'location' => 'Dwarka, New Delhi',
            'courses' => ['B.Tech', 'M.Tech', 'MCA', 'PhD'],
            'total_seats' => 600,
            'page' => '/usict-admission.php'
        ],
        [
            'code' => 'USAR',
            'name' => 'University School of Automation & Robotics (USAR)',
            'short_name' => 'USAR',
            'location' => 'Dwarka, New Delhi',
            'courses' => ['B.Tech'],
            'total_seats' => 300,
            'page' => '/usar-admission.php'
        ],
        [
            'code' => 'USLS',
            'name' => 'University School of Law & Legal Studies (USLS)',
            'short_name' => 'USLS',
            'location' => 'Dwarka, New Delhi',
            'courses' => ['BA LLB', 'BBA LLB', 'LLM'],
            'total_seats' => 200,
            'page' => '/usls-admission.php'
        ],
        [
            'code' => 'USMS',
            'name' => 'University School of Management Studies (USMS)',
            'short_name' => 'USMS',
            'location' => 'Dwarka, New Delhi',
            'courses' => ['MBA'],
            'total_seats' => 150,
            'page' => '/usms-admission.php'
        ],
        // === Top Engineering Colleges ===
        [
            'code' => 'MAIT',
            'name' => 'Maharaja Agrasen Institute of Technology (MAIT)',
            'short_name' => 'MAIT',
            'location' => 'Rohini, New Delhi',
            'courses' => ['B.Tech', 'MBA'],
            'total_seats' => 720,
            'page' => '/mait-admission.php'
        ],
        [
            'code' => 'MSIT',
            'name' => 'Maharaja Surajmal Institute of Technology (MSIT)',
            'short_name' => 'MSIT',
            'location' => 'Janakpuri, New Delhi',
            'courses' => ['B.Tech', 'MBA'],
            'total_seats' => 600,
            'page' => '/msit-admission.php'
        ],
        [
            'code' => 'BPIT',
            'name' => 'Bhagwan Parshuram Institute of Technology (BPIT)',
            'short_name' => 'BPIT',
            'location' => 'Rohini, New Delhi',
            'courses' => ['B.Tech', 'BBA', 'MBA'],
            'total_seats' => 600,
            'page' => '/BPIT.php'
        ],
        [
            'code' => 'BVP',
            'name' => 'Bharati Vidyapeeth College of Engineering (BVP)',
            'short_name' => 'BVP',
            'location' => 'Paschim Vihar, New Delhi',
            'courses' => ['B.Tech', 'BJMC', 'MCA'],
            'total_seats' => 540,
            'page' => '/BVP.php'
        ],
        [
            'code' => 'GTBIT',
            'name' => 'Guru Tegh Bahadur Institute of Technology (GTBIT)',
            'short_name' => 'GTBIT',
            'location' => 'Rajouri Garden, New Delhi',
            'courses' => ['B.Tech'],
            'total_seats' => 420,
            'page' => '/gtbit-admission.php'
        ],
        [
            'code' => 'ADGITM',
            'name' => 'Apeejay Stya University - Delhi Group of Institutions (ADGITM)',
            'short_name' => 'ADGITM',
            'location' => 'Dwarka, New Delhi',
            'courses' => ['B.Tech', 'MBA'],
            'total_seats' => 420,
            'page' => '/adgitm-admission.php'
        ],
        // === Management & Multi-Discipline Colleges ===
        [
            'code' => 'VIPS',
            'name' => 'Vivekananda Institute of Professional Studies (VIPS)',
            'short_name' => 'VIPS',
            'location' => 'Pitampura, New Delhi',
            'courses' => ['BBA', 'BA LLB', 'BBA LLB', 'BJMC', 'B.Com'],
            'total_seats' => 800,
            'page' => '/vips-pitampura-courses.php'
        ],
        [
            'code' => 'MAIMS',
            'name' => 'Maharaja Agrasen Institute of Management Studies (MAIMS)',
            'short_name' => 'MAIMS',
            'location' => 'Rohini, New Delhi',
            'courses' => ['BBA', 'BCA', 'B.Com', 'BJMC'],
            'total_seats' => 600,
            'page' => '/maims-admission.php'
        ],
        [
            'code' => 'MSI',
            'name' => 'Maharaja Surajmal Institute (MSI)',
            'short_name' => 'MSI',
            'location' => 'Janakpuri, New Delhi',
            'courses' => ['BBA', 'BCA', 'B.Com'],
            'total_seats' => 480,
            'page' => '/msi-admission.php'
        ],
        [
            'code' => 'JIMS',
            'name' => 'Jagannath International Management School (JIMS)',
            'short_name' => 'JIMS',
            'location' => 'Rohini, New Delhi',
            'courses' => ['BBA', 'B.Com'],
            'total_seats' => 360,
            'page' => '/jims-admission.php'
        ],
        [
            'code' => 'IITM',
            'name' => 'Institute of Innovation in Technology & Management (IITM)',
            'short_name' => 'IITM',
            'location' => 'Janakpuri, New Delhi',
            'courses' => ['BBA', 'BCA', 'B.Com', 'MBA'],
            'total_seats' => 480,
            'page' => '/iitm-admission.php'
        ],
        [
            'code' => 'HMR',
            'name' => 'HMR Institute of Technology & Management (HMR)',
            'short_name' => 'HMR',
            'location' => 'Hamidpur, New Delhi',
            'courses' => ['B.Tech', 'BBA', 'BCA'],
            'total_seats' => 480,
            'page' => '/hmr-admission.php'
        ],
        [
            'code' => 'MABS',
            'name' => 'Maharaja Agrasen Business School (MABS)',
            'short_name' => 'MABS',
            'location' => 'Rohini, New Delhi',
            'courses' => ['MBA'],
            'total_seats' => 120,
            'page' => '/mabs-admission.php'
        ],
        [
            'code' => 'DIAS',
            'name' => 'Delhi Institute of Advanced Studies (DIAS)',
            'short_name' => 'DIAS',
            'location' => 'Rohini, New Delhi',
            'courses' => ['BBA', 'B.Com'],
            'total_seats' => 120,
            'page' => '/dias-admission.php'
        ],
        [
            'code' => 'Trinity',
            'name' => 'Trinity Institute of Professional Studies (TIPS)',
            'short_name' => 'TIPS',
            'location' => 'Dwarka, New Delhi',
            'courses' => ['BBA', 'BA LLB', 'BBA LLB', 'BCA', 'B.Com', 'BJMC'],
            'total_seats' => 600,
            'page' => '/tips-admission.php'
        ],
        // === 25 New Colleges ===
        [
            'code' => 'DTC',
            'name' => 'Delhi Technical Campus',
            'short_name' => 'DTC',
            'location' => 'Greater Noida, UP',
            'courses' => ['B.Tech CSE', 'B.Tech AIML', 'B.Tech AI&DS', 'B.Tech CST', 'BCA'],
            'total_seats' => 1020,
            'page' => '/dtc-admission.php'
        ],
        [
            'code' => 'JEMTEC',
            'name' => 'JIMS Engineering Management Technical Campus',
            'short_name' => 'JEMTEC',
            'location' => 'Greater Noida, UP',
            'courses' => ['B.Tech', 'BBA', 'BA LLB', 'BBA LLB', 'BCA', 'B.Com'],
            'total_seats' => 1000,
            'page' => '/jemtec-admission.php'
        ],
        [
            'code' => 'Echelon',
            'name' => 'Echelon Institute of Technology',
            'short_name' => 'Echelon',
            'location' => 'Faridabad, Haryana',
            'courses' => ['B.Tech', 'BBA', 'MCA', 'BCA'],
            'total_seats' => 1000,
            'page' => '/echelon-admission.php'
        ],
        [
            'code' => 'DME',
            'name' => 'Delhi Metropolitan Education',
            'short_name' => 'DME',
            'location' => 'Noida, UP',
            'courses' => ['BBA', 'BA LLB', 'BBA LLB', 'BA(JMC)', 'LLM'],
            'total_seats' => 900,
            'page' => '/dme-admission.php'
        ],
        [
            'code' => 'CPJ',
            'name' => 'Chanderprabhu Jain College',
            'short_name' => 'CPJ',
            'location' => 'Narela, New Delhi',
            'courses' => ['BBA', 'BA LLB', 'BBA LLB', 'BCA', 'LLB', 'LLM'],
            'total_seats' => 800,
            'page' => '/cpj-admission.php'
        ],
        [
            'code' => 'Fairfield',
            'name' => 'Fairfield Institute of Management & Technology',
            'short_name' => 'Fairfield',
            'location' => 'Kapashera, New Delhi',
            'courses' => ['B.Tech', 'BBA', 'BA LLB', 'BBA LLB', 'BCA', 'B.Com', 'BJMC', 'B.Ed'],
            'total_seats' => 900,
            'page' => '/fairfield-admission.php'
        ],
        [
            'code' => 'RDIAS',
            'name' => 'Rukmini Devi Institute of Advanced Studies',
            'short_name' => 'RDIAS',
            'location' => 'Rohini, New Delhi',
            'courses' => ['BBA', 'B.Com'],
            'total_seats' => 420,
            'page' => '/rdias-admission.php'
        ],
        [
            'code' => 'GIBS',
            'name' => 'Gitarattan International Business School',
            'short_name' => 'GIBS',
            'location' => 'Rohini, New Delhi',
            'courses' => ['BBA', 'BA LLB', 'BBA LLB', 'LLM'],
            'total_seats' => 680,
            'page' => '/gibs-admission.php'
        ],
        [
            'code' => 'JIMS_Kalkaji',
            'name' => 'Jagannath International Management School Kalkaji',
            'short_name' => 'JIMS Kalkaji',
            'location' => 'Kalkaji, New Delhi',
            'courses' => ['BBA', 'B.Com'],
            'total_seats' => 240,
            'page' => '/jims-kalkaji-admission.php'
        ],
        [
            'code' => 'JIMS_VK',
            'name' => 'Jagannath International Management School Vasant Kunj',
            'short_name' => 'JIMS VK',
            'location' => 'Vasant Kunj, New Delhi',
            'courses' => ['BBA', 'BCA'],
            'total_seats' => 360,
            'page' => '/jims-vasant-kunj-admission.php'
        ],
        [
            'code' => 'Ideal',
            'name' => 'Ideal Institute of Management and Technology',
            'short_name' => 'Ideal',
            'location' => 'Karkardooma, New Delhi',
            'courses' => ['BBA', 'BA LLB', 'BBA LLB', 'BCA', 'LLM'],
            'total_seats' => 400,
            'page' => '/ideal-admission.php'
        ],
        [
            'code' => 'KCC',
            'name' => 'KCC Institute of Legal & Higher Education',
            'short_name' => 'KCC',
            'location' => 'Greater Noida, UP',
            'courses' => ['BBA', 'BA LLB', 'BBA LLB', 'BCA', 'B.Com'],
            'total_seats' => 620,
            'page' => '/kcc-admission.php'
        ],
        [
            'code' => 'Tecnia',
            'name' => 'Tecnia Institute of Advanced Studies',
            'short_name' => 'Tecnia',
            'location' => 'Rohini, New Delhi',
            'courses' => ['BBA', 'MCA', 'BCA', 'B.Com', 'BJMC'],
            'total_seats' => 600,
            'page' => '/tecnia-admission.php'
        ],
        [
            'code' => 'NDIM',
            'name' => 'New Delhi Institute of Management',
            'short_name' => 'NDIM',
            'location' => 'Tughlakabad, New Delhi',
            'courses' => ['BBA', 'BCA'],
            'total_seats' => 330,
            'page' => '/ndim-admission.php'
        ],
        [
            'code' => 'MERI',
            'name' => 'Management Education & Research Institute',
            'short_name' => 'MERI',
            'location' => 'Janakpuri, New Delhi',
            'courses' => ['BBA', 'BCA', 'B.Com', 'BJMC'],
            'total_seats' => 600,
            'page' => '/meri-admission.php'
        ],
        [
            'code' => 'KasturiRam',
            'name' => 'Kasturi Ram College of Higher Education',
            'short_name' => 'Kasturi Ram',
            'location' => 'Narela, New Delhi',
            'courses' => ['BBA', 'BCA', 'B.Com', 'B.Ed'],
            'total_seats' => 300,
            'page' => '/kasturi-ram-admission.php'
        ],
        [
            'code' => 'Lingayas',
            'name' => 'Lingayas Lalita Devi Institute',
            'short_name' => 'Lingayas',
            'location' => 'Mandi, New Delhi',
            'courses' => ['BBA', 'BCA', 'B.Com', 'BJMC'],
            'total_seats' => 300,
            'page' => '/lingayas-admission.php'
        ],
        [
            'code' => 'DonBosco',
            'name' => 'Don Bosco Institute of Technology',
            'short_name' => 'Don Bosco',
            'location' => 'Okhla, New Delhi',
            'courses' => ['BBA', 'MCA', 'BCA', 'B.Com'],
            'total_seats' => 420,
            'page' => '/don-bosco-admission.php'
        ],
        [
            'code' => 'GTB4CEC',
            'name' => 'Guru Tegh Bahadur 4th Centenary Engineering College',
            'short_name' => 'GTB4CEC',
            'location' => 'Rajouri Garden, New Delhi',
            'courses' => ['B.Tech'],
            'total_seats' => 360,
            'page' => '/gtb4cec-admission.php'
        ],
        [
            'code' => 'BCIPS',
            'name' => 'Banarsidas Chandiwala Institute of Professional Studies',
            'short_name' => 'BCIPS',
            'location' => 'Dwarka/Kalkaji, New Delhi',
            'courses' => ['BBA', 'MCA', 'BCA', 'B.Com'],
            'total_seats' => 300,
            'page' => '/bcips-admission.php'
        ],
        [
            'code' => 'SGTBIMIT',
            'name' => 'Sri Guru Tegh Bahadur Institute of Management & IT',
            'short_name' => 'SGTBIMIT',
            'location' => 'GTK Road, New Delhi',
            'courses' => ['BBA', 'BCA', 'B.Com'],
            'total_seats' => 510,
            'page' => '/sgtbimit-admission.php'
        ],
        [
            'code' => 'Sirifort',
            'name' => 'Sirifort Institute of Management Studies',
            'short_name' => 'Sirifort',
            'location' => 'Rohini, New Delhi',
            'courses' => ['BBA', 'BCA', 'B.Com'],
            'total_seats' => 360,
            'page' => '/sirifort-admission.php'
        ],
        [
            'code' => 'GNIT',
            'name' => 'Greater Noida Institute of Technology',
            'short_name' => 'GNIT',
            'location' => 'Greater Noida, UP',
            'courses' => ['B.Tech'],
            'total_seats' => 210,
            'page' => '/gnit-admission.php'
        ],
    ],

    'faq' => [
        [
            'question' => 'How do I apply for B.Tech in IP University 2026?',
            'answer' => 'B.Tech admission in IPU is based on JEE Main rank. You need to appear in JEE Main, qualify in the top 2,50,000 ranks, register on the GGSIPU official counselling portal, fill your choice of colleges and branches, and participate in the centralized counselling process. Call us at 9899991342 for step-by-step guidance.'
        ],
        [
            'question' => 'What is the cutoff for MSIT B.Tech 2026?',
            'answer' => 'MSIT cutoffs vary by branch and category. CSE typically has the highest cutoff (around 5,000-8,000 in JEE Main rank), while other branches like Civil have lower cutoffs. For exact 2026 cutoffs, contact us at 9899991342.'
        ],
        [
            'question' => 'What is management quota admission in IPU?',
            'answer' => 'Management quota allows direct admission to IPU affiliated colleges outside of the regular JEE/cutoff-based counselling process. You can apply directly to colleges for available management seats. Our counsellors can guide you through the management quota admission process. Call 9899991342.'
        ],
        [
            'question' => 'Which are the best engineering colleges under IP University?',
            'answer' => 'Top engineering colleges under GGSIPU include: MSIT (NIRF rank 35), MAIT (NIRF rank 38), BVP (NIRF rank 45), BPIT, and VIPS. All offer excellent placements, quality faculty, and modern infrastructure.'
        ],
        [
            'question' => 'What is the average package in MSIT B.Tech?',
            'answer' => 'MSIT B.Tech average package is around ₹8-12 LPA. Top students get packages up to ₹18-25 LPA from companies like Microsoft, Google, Deloitte, Amazon, and TCS.'
        ],
        [
            'question' => 'How much does admissions counselling cost?',
            'answer' => '100% FREE! Our counselling service is completely free. We guide students through the entire admission process, from eligibility check to final seat allotment, at no charge.'
        ]
    ],

    'admission_timeline' => [
        [
            'event' => 'JEE Main Exam',
            'timeline' => 'April 2026',
            'details' => 'Complete 2-day multiple choice examination'
        ],
        [
            'event' => 'JEE Main Result',
            'timeline' => 'Late April 2026',
            'details' => 'Results announced with AIR rank and percentile'
        ],
        [
            'event' => 'GGSIPU Registration Opens',
            'timeline' => 'May 2026',
            'details' => 'Register on official GGSIPU counselling portal'
        ],
        [
            'event' => 'Choice Filling Begins',
            'timeline' => 'May 2026',
            'details' => 'Fill college and branch preferences (critical step)'
        ],
        [
            'event' => 'Seat Allotment Rounds',
            'timeline' => 'June 2026',
            'details' => 'Merit-based seat allocation in multiple rounds'
        ],
        [
            'event' => 'Document Verification',
            'timeline' => 'July 2026',
            'details' => 'Submit documents and verify eligibility'
        ],
        [
            'event' => 'College Admission',
            'timeline' => 'July-August 2026',
            'details' => 'Complete admission formalities with allotted college'
        ]
    ],

    'statistics' => [
        'total_students_guided' => '5000+',
        'free_counselling' => '100%',
        'admission_success_rate' => '99%',
        'years_of_experience' => '10+',
        'colleges_covered' => '60+',
        'colleges_with_pages' => 46,
        'total_pages' => 50,
        'top_courses' => ['B.Tech', 'MBA', 'Law', 'BBA', 'BCA', 'BJMC', 'B.Com', 'B.Ed', 'LLB', 'LLM'],
        'placement_average' => '90%+',
        'average_salary_btech' => '₹8-12 LPA',
        'average_salary_mba' => '₹15-20 LPA'
    ]
];

// Pages directory with content summaries
$pages = [
    ['url' => 'https://ipu.co.in/', 'title' => 'IP University Admission 2026', 'type' => 'homepage', 'keywords' => ['ipu admission', 'ip university admission 2026']],
    ['url' => 'https://ipu.co.in/IPU-B-Tech-admission-2026.php', 'title' => 'B.Tech Admission 2026', 'type' => 'admission-guide', 'keywords' => ['ipu btech admission', 'btech admission through cuet']],
    ['url' => 'https://ipu.co.in/mba-admission-ip-university.php', 'title' => 'MBA Admission 2026', 'type' => 'admission-guide', 'keywords' => ['ipu mba admission']],
    ['url' => 'https://ipu.co.in/IPU-Law-Admission-2026.php', 'title' => 'Law Admission 2026', 'type' => 'admission-guide', 'keywords' => ['ipu ballb admission', 'ipu bballb admission']],
    ['url' => 'https://ipu.co.in/ipu-bba-admission.php', 'title' => 'BBA Admission 2026', 'type' => 'admission-guide', 'keywords' => ['ipu bba admission']],
    ['url' => 'https://ipu.co.in/bcom-admission-ipu.php', 'title' => 'B.Com Admission 2026', 'type' => 'admission-guide', 'keywords' => ['ipu b.com admission']],
    ['url' => 'https://ipu.co.in/ba-english-admission-ipu.php', 'title' => 'BA English Admission', 'type' => 'admission-guide', 'keywords' => ['ipu ba english admission']],
    ['url' => 'https://ipu.co.in/ba-economics-admission-ipu.php', 'title' => 'BA Economics Admission', 'type' => 'admission-guide', 'keywords' => ['ipu ba eco admission']],
    ['url' => 'https://ipu.co.in/GGSIPU-counselling-for-B-Tech-admission.php', 'title' => 'GGSIPU Counselling Process', 'type' => 'process-guide', 'keywords' => ['ipu counselling']],
    ['url' => 'https://ipu.co.in/IP-University-management-quota-admission-eligibility-criteria.php', 'title' => 'Management Quota Admission', 'type' => 'admission-guide', 'keywords' => ['ipu management quota', 'management quota admission']],
    ['url' => 'https://ipu.co.in/ipu-helpline-contact-number.php', 'title' => 'IPU Helpline & Contact', 'type' => 'contact', 'keywords' => ['ipu helpline', 'ipu contact number', 'ggsipu phone number']],
    ['url' => 'https://ipu.co.in/mait-admission.php', 'title' => 'MAIT Admission', 'type' => 'college-profile', 'keywords' => ['mait', 'mait admission']],
    ['url' => 'https://ipu.co.in/msit-admission.php', 'title' => 'MSIT Admission', 'type' => 'college-profile', 'keywords' => ['msit', 'msit admission']],
    ['url' => 'https://ipu.co.in/BPIT.php', 'title' => 'BPIT College Guide', 'type' => 'college-profile', 'keywords' => ['bpit']],
    ['url' => 'https://ipu.co.in/BVP.php', 'title' => 'BVP College Guide', 'type' => 'college-profile', 'keywords' => ['bvp']],
    ['url' => 'https://ipu.co.in/vips-pitampura-courses.php', 'title' => 'VIPS Pitampura', 'type' => 'college-profile', 'keywords' => ['vips']],
    ['url' => 'https://ipu.co.in/usict-admission.php', 'title' => 'USICT Admission', 'type' => 'college-profile', 'keywords' => ['usict']],
    ['url' => 'https://ipu.co.in/usar-admission.php', 'title' => 'USAR Admission', 'type' => 'college-profile', 'keywords' => ['usar']],
    ['url' => 'https://ipu.co.in/usls-admission.php', 'title' => 'USLS Admission', 'type' => 'college-profile', 'keywords' => ['usls']],
    ['url' => 'https://ipu.co.in/usms-admission.php', 'title' => 'USMS MBA Admission', 'type' => 'college-profile', 'keywords' => ['usms']],
    ['url' => 'https://ipu.co.in/college-admission-delhi.php', 'title' => 'College Admission in Delhi', 'type' => 'location-guide', 'keywords' => ['college admission in delhi']],
    ['url' => 'https://ipu.co.in/top-btech-colleges-delhi.php', 'title' => 'Top B.Tech Colleges Delhi', 'type' => 'comparison', 'keywords' => ['top btech college in delhi', 'btech college in delhi']],
    ['url' => 'https://ipu.co.in/ipu-colleges-list.php', 'title' => 'All IPU Colleges List', 'type' => 'directory', 'keywords' => ['list of college', 'top college', 'best college']],
    ['url' => 'https://ipu.co.in/blog.php', 'title' => 'IPU Admission Blog', 'type' => 'blog', 'keywords' => []],
    // Existing college pages
    ['url' => 'https://ipu.co.in/gtbit-admission.php', 'title' => 'GTBIT Admission', 'type' => 'college-profile', 'keywords' => ['gtbit', 'gtbit admission']],
    ['url' => 'https://ipu.co.in/adgitm-admission.php', 'title' => 'ADGITM Admission', 'type' => 'college-profile', 'keywords' => ['adgitm', 'adgitm admission']],
    ['url' => 'https://ipu.co.in/maims-admission.php', 'title' => 'MAIMS Admission', 'type' => 'college-profile', 'keywords' => ['maims', 'maims admission']],
    ['url' => 'https://ipu.co.in/msi-admission.php', 'title' => 'MSI Admission', 'type' => 'college-profile', 'keywords' => ['msi', 'msi admission']],
    ['url' => 'https://ipu.co.in/dias-admission.php', 'title' => 'DIAS Admission', 'type' => 'college-profile', 'keywords' => ['dias', 'dias admission']],
    ['url' => 'https://ipu.co.in/jims-admission.php', 'title' => 'JIMS Admission', 'type' => 'college-profile', 'keywords' => ['jims', 'jims rohini admission']],
    ['url' => 'https://ipu.co.in/iitm-admission.php', 'title' => 'IITM Admission', 'type' => 'college-profile', 'keywords' => ['iitm', 'iitm janakpuri admission']],
    ['url' => 'https://ipu.co.in/hmr-admission.php', 'title' => 'HMR Admission', 'type' => 'college-profile', 'keywords' => ['hmr', 'hmr admission']],
    ['url' => 'https://ipu.co.in/mabs-admission.php', 'title' => 'MABS Admission', 'type' => 'college-profile', 'keywords' => ['mabs', 'mabs mba admission']],
    ['url' => 'https://ipu.co.in/trinity-law-admission.php', 'title' => 'Trinity / TIPS Law Admission', 'type' => 'college-profile', 'keywords' => ['tips', 'trinity law admission']],
    // Additional course pages
    ['url' => 'https://ipu.co.in/bca-admission-ipu.php', 'title' => 'BCA Admission 2026', 'type' => 'admission-guide', 'keywords' => ['ipu bca admission']],
    ['url' => 'https://ipu.co.in/barch-admission-ipu.php', 'title' => 'B.Arch Admission 2026', 'type' => 'admission-guide', 'keywords' => ['ipu barch admission']],
    ['url' => 'https://ipu.co.in/llm-admission-ipu.php', 'title' => 'LLM Admission 2026', 'type' => 'admission-guide', 'keywords' => ['ipu llm admission']],
    // New college pages (25 colleges)
    ['url' => 'https://ipu.co.in/dtc-admission.php', 'title' => 'DTC Admission', 'type' => 'college-profile', 'keywords' => ['dtc', 'delhi technical campus admission']],
    ['url' => 'https://ipu.co.in/jemtec-admission.php', 'title' => 'JEMTEC Admission', 'type' => 'college-profile', 'keywords' => ['jemtec', 'jemtec admission']],
    ['url' => 'https://ipu.co.in/echelon-admission.php', 'title' => 'Echelon Admission', 'type' => 'college-profile', 'keywords' => ['echelon', 'echelon institute admission']],
    ['url' => 'https://ipu.co.in/dme-admission.php', 'title' => 'DME Admission', 'type' => 'college-profile', 'keywords' => ['dme', 'delhi metropolitan education admission']],
    ['url' => 'https://ipu.co.in/cpj-admission.php', 'title' => 'CPJ College Admission', 'type' => 'college-profile', 'keywords' => ['cpj', 'chanderprabhu jain admission']],
    ['url' => 'https://ipu.co.in/fairfield-admission.php', 'title' => 'Fairfield Admission', 'type' => 'college-profile', 'keywords' => ['fairfield', 'fairfield institute admission']],
    ['url' => 'https://ipu.co.in/rdias-admission.php', 'title' => 'RDIAS Admission', 'type' => 'college-profile', 'keywords' => ['rdias', 'rukmini devi admission']],
    ['url' => 'https://ipu.co.in/gibs-admission.php', 'title' => 'GIBS Admission', 'type' => 'college-profile', 'keywords' => ['gibs', 'gitarattan admission']],
    ['url' => 'https://ipu.co.in/jims-kalkaji-admission.php', 'title' => 'JIMS Kalkaji Admission', 'type' => 'college-profile', 'keywords' => ['jims kalkaji', 'jims kalkaji admission']],
    ['url' => 'https://ipu.co.in/jims-vasant-kunj-admission.php', 'title' => 'JIMS Vasant Kunj Admission', 'type' => 'college-profile', 'keywords' => ['jims vasant kunj', 'jims vk admission']],
    ['url' => 'https://ipu.co.in/ideal-admission.php', 'title' => 'Ideal Institute Admission', 'type' => 'college-profile', 'keywords' => ['ideal', 'ideal institute admission']],
    ['url' => 'https://ipu.co.in/kcc-admission.php', 'title' => 'KCC Institute Admission', 'type' => 'college-profile', 'keywords' => ['kcc', 'kcc institute admission']],
    ['url' => 'https://ipu.co.in/tecnia-admission.php', 'title' => 'Tecnia Admission', 'type' => 'college-profile', 'keywords' => ['tecnia', 'tecnia institute admission']],
    ['url' => 'https://ipu.co.in/ndim-admission.php', 'title' => 'NDIM Admission', 'type' => 'college-profile', 'keywords' => ['ndim', 'new delhi institute of management admission']],
    ['url' => 'https://ipu.co.in/tips-admission.php', 'title' => 'TIPS Admission', 'type' => 'college-profile', 'keywords' => ['tips', 'trinity institute admission']],
    ['url' => 'https://ipu.co.in/meri-admission.php', 'title' => 'MERI Admission', 'type' => 'college-profile', 'keywords' => ['meri', 'meri institute admission']],
    ['url' => 'https://ipu.co.in/kasturi-ram-admission.php', 'title' => 'Kasturi Ram Admission', 'type' => 'college-profile', 'keywords' => ['kasturi ram', 'kasturi ram college admission']],
    ['url' => 'https://ipu.co.in/lingayas-admission.php', 'title' => 'Lingayas Admission', 'type' => 'college-profile', 'keywords' => ['lingayas', 'lingayas lalita devi admission']],
    ['url' => 'https://ipu.co.in/don-bosco-admission.php', 'title' => 'Don Bosco Admission', 'type' => 'college-profile', 'keywords' => ['don bosco', 'don bosco institute admission']],
    ['url' => 'https://ipu.co.in/gtb4cec-admission.php', 'title' => 'GTB4CEC Admission', 'type' => 'college-profile', 'keywords' => ['gtb4cec', 'guru tegh bahadur 4th centenary admission']],
    ['url' => 'https://ipu.co.in/bcips-admission.php', 'title' => 'BCIPS Admission', 'type' => 'college-profile', 'keywords' => ['bcips', 'banarsidas chandiwala admission']],
    ['url' => 'https://ipu.co.in/sgtbimit-admission.php', 'title' => 'SGTBIMIT Admission', 'type' => 'college-profile', 'keywords' => ['sgtbimit', 'sri guru tegh bahadur admission']],
    ['url' => 'https://ipu.co.in/sirifort-admission.php', 'title' => 'Sirifort Admission', 'type' => 'college-profile', 'keywords' => ['sirifort', 'sirifort institute admission']],
    ['url' => 'https://ipu.co.in/gnit-admission.php', 'title' => 'GNIT Admission', 'type' => 'college-profile', 'keywords' => ['gnit', 'greater noida institute admission']],
];

// Admission processes (structured for AI extraction)
$processes = [
    'btech' => [
        'title' => 'B.Tech Admission Process at IP University',
        'steps' => [
            'Appear in JEE Main exam (April 2026)',
            'Check JEE Main result and rank (Late April)',
            'Register on GGSIPU counselling portal (May)',
            'Fill college and branch preferences during choice filling (May-June)',
            'Wait for seat allotment result (June)',
            'Accept/freeze/float the allotted seat',
            'Complete document verification at allotted college (July)',
            'Pay admission fee and complete formalities (July-August)',
        ],
        'contact' => 'Call 9899991342 for free step-by-step guidance',
    ],
    'mba' => [
        'title' => 'MBA Admission Process at IP University',
        'steps' => [
            'Appear in CAT or CMAT exam',
            'Register on GGSIPU counselling portal with CAT/CMAT score',
            'Fill college preferences during choice filling',
            'Wait for seat allotment',
            'Complete document verification and admission',
        ],
        'contact' => 'Call 9899991342 for free MBA admission guidance',
    ],
    'law' => [
        'title' => 'Law (BA LLB / BBA LLB) Admission at IP University',
        'steps' => [
            'Appear in CLAT exam',
            'Register on GGSIPU counselling portal with CLAT score',
            'Fill college preferences (USLLS, VIPS, etc.)',
            'Wait for seat allotment',
            'Complete document verification and admission',
        ],
        'contact' => 'Call 9899991342 for free law admission guidance',
    ],
];

// Route requests based on action parameter
switch ($action) {
    case 'overview':
        $response = $knowledge_base['overview'];
        break;
    case 'courses':
        $response = [
            'total' => count($knowledge_base['courses']),
            'courses' => $knowledge_base['courses']
        ];
        break;
    case 'colleges':
        $response = [
            'total' => count($knowledge_base['colleges']),
            'colleges' => $knowledge_base['colleges']
        ];
        break;
    case 'faq':
        $response = [
            'total' => count($knowledge_base['faq']),
            'faqs' => $knowledge_base['faq']
        ];
        break;
    case 'timeline':
        $response = [
            'year' => 2026,
            'events' => $knowledge_base['admission_timeline']
        ];
        break;
    case 'stats':
        $response = $knowledge_base['statistics'];
        break;
    case 'pages':
        $response = [
            'total' => count($pages),
            'pages' => $pages
        ];
        break;
    case 'processes':
        $response = $processes;
        break;
    case 'all':
        $response = array_merge($knowledge_base, ['pages' => $pages, 'processes' => $processes]);
        break;
    default:
        $response = [
            'error' => 'Unknown action',
            'available_actions' => ['overview', 'courses', 'colleges', 'faq', 'timeline', 'stats', 'pages', 'processes', 'all'],
            'example_url' => 'https://ipu.co.in/api/agent-data.php?action=courses'
        ];
        http_response_code(400);
}

echo json_encode(
    [
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => $response,
        'meta' => [
            'source' => 'ipu.co.in',
            'version' => '3.0',
            'for_agents' => ['ChatGPT', 'Claude', 'Gemini', 'Bard', 'LLaMA', 'All AI Models']
        ]
    ],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
);
?>
