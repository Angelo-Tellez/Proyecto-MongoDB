<?php
/**
 * seed_mongo.php — Carga los datos iniciales en MongoDB
 *
 * Ejecutar UNA sola vez desde la raíz del proyecto:
 *   php bd/seed_mongo.php
 *
 * Requiere vendor/autoload.php (composer require mongodb/mongodb)
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../orm/conexion.php';

$db = obtenerConexion();

// ── Limpiar colecciones ──────────────────────────────────────────────────────
$db->categorias->drop();
$db->niveles->drop();
$db->instructores->drop();
$db->cursos->drop();
echo "Colecciones limpiadas.\n";

// ── CATEGORIAS ───────────────────────────────────────────────────────────────
$catDocs = [
    ['nombre' => 'Desarrollo Web',          'descripcion' => 'Frontend, backend, frameworks y tecnologias web modernas.',          'estatus' => 'activo'],
    ['nombre' => 'Desarrollo Movil',         'descripcion' => 'Aplicaciones para Android, iOS y desarrollo multiplataforma.',       'estatus' => 'activo'],
    ['nombre' => 'Inteligencia Artificial',  'descripcion' => 'Machine learning, deep learning, NLP y vision por computadora.',     'estatus' => 'activo'],
    ['nombre' => 'Ciencia de Datos',         'descripcion' => 'Analisis, visualizacion de datos, estadistica y Big Data.',          'estatus' => 'activo'],
    ['nombre' => 'Ciberseguridad',           'descripcion' => 'Ethical hacking, seguridad en redes y proteccion de sistemas.',      'estatus' => 'activo'],
    ['nombre' => 'Diseno UI/UX',             'descripcion' => 'Diseno de interfaces, experiencia de usuario y prototipado.',        'estatus' => 'activo'],
    ['nombre' => 'Diseno Grafico',           'descripcion' => 'Ilustracion digital, branding, tipografia y composicion visual.',    'estatus' => 'activo'],
    ['nombre' => 'Fotografia y Video',       'descripcion' => 'Tecnicas fotograficas, edicion de video y produccion audiovisual.',  'estatus' => 'activo'],
    ['nombre' => 'Musica y Produccion',      'descripcion' => 'Produccion musical, mezcla, masterizacion e instrumentos.',          'estatus' => 'inactivo'],
    ['nombre' => 'Marketing Digital',        'descripcion' => 'SEO, SEM, redes sociales, email marketing y analitica web.',         'estatus' => 'activo'],
    ['nombre' => 'Negocios y Emprendimiento','descripcion' => 'Startups, plan de negocios, finanzas y liderazgo empresarial.',      'estatus' => 'activo'],
    ['nombre' => 'Finanzas Personales',      'descripcion' => 'Ahorro, inversion, criptomonedas y planificacion financiera.',       'estatus' => 'activo'],
    ['nombre' => 'Idiomas',                  'descripcion' => 'Ingles, frances, mandarin, japones y mas idiomas del mundo.',        'estatus' => 'inactivo'],
    ['nombre' => 'Fitness y Salud',          'descripcion' => 'Entrenamiento, nutricion, yoga, meditacion y bienestar integral.',   'estatus' => 'activo'],
    ['nombre' => 'Cocina y Gastronomia',     'descripcion' => 'Tecnicas culinarias, reposteria, cocinas del mundo y nutricion.',    'estatus' => 'activo'],
    ['nombre' => 'Desarrollo Personal',      'descripcion' => 'Productividad, inteligencia emocional, habitos y liderazgo.',        'estatus' => 'activo'],
    ['nombre' => 'Videojuegos',              'descripcion' => 'Desarrollo de videojuegos, diseno de niveles y game art.',           'estatus' => 'activo'],
    ['nombre' => 'DevOps y Cloud',           'descripcion' => 'Docker, Kubernetes, AWS, Azure, CI/CD y automatizacion.',            'estatus' => 'inactivo'],
    ['nombre' => 'Bases de Datos',           'descripcion' => 'SQL, NoSQL, modelado de datos y administracion de BD.',              'estatus' => 'activo'],
    ['nombre' => 'Arquitectura y Diseno 3D', 'descripcion' => 'AutoCAD, Blender, renders, modelado 3D y animacion.',               'estatus' => 'activo'],
];
foreach ($catDocs as &$d) { $d['fecha_creacion'] = new MongoDB\BSON\UTCDateTime(); }
$catResult = $db->categorias->insertMany($catDocs);
$catIds    = $catResult->getInsertedIds();
echo "Categorias insertadas: " . count($catIds) . "\n";

$catMap = [];
foreach ($catIds as $i => $oid) {
    $catMap[$catDocs[$i]['nombre']] = (string)$oid;
}

// ── NIVELES ──────────────────────────────────────────────────────────────────
$nivDocs = [
    ['nombre' => 'Principiante', 'orden' => 1, 'estatus' => 'activo'],
    ['nombre' => 'Intermedio',   'orden' => 2, 'estatus' => 'activo'],
    ['nombre' => 'Avanzado',     'orden' => 3, 'estatus' => 'activo'],
    ['nombre' => 'Experto',      'orden' => 4, 'estatus' => 'activo'],
];
$db->niveles->insertMany($nivDocs);
echo "Niveles insertados: " . count($nivDocs) . "\n";

// ── INSTRUCTORES ─────────────────────────────────────────────────────────────
$instDocs = [
    ['nombre' => 'Carlos',    'apellido' => 'Mendoza',    'email' => 'carlos.mendoza@academia.com',    'estatus' => 'activo'],
    ['nombre' => 'Ana',       'apellido' => 'Lopez',      'email' => 'ana.lopez@academia.com',          'estatus' => 'activo'],
    ['nombre' => 'Roberto',   'apellido' => 'Garcia',     'email' => 'roberto.garcia@academia.com',     'estatus' => 'activo'],
    ['nombre' => 'Sofia',     'apellido' => 'Hernandez',  'email' => 'sofia.hernandez@academia.com',    'estatus' => 'activo'],
    ['nombre' => 'Miguel',    'apellido' => 'Torres',     'email' => 'miguel.torres@academia.com',      'estatus' => 'activo'],
    ['nombre' => 'Laura',     'apellido' => 'Ramirez',    'email' => 'laura.ramirez@academia.com',      'estatus' => 'activo'],
    ['nombre' => 'Diego',     'apellido' => 'Fernandez',  'email' => 'diego.fernandez@academia.com',    'estatus' => 'activo'],
    ['nombre' => 'Valeria',   'apellido' => 'Castro',     'email' => 'valeria.castro@academia.com',     'estatus' => 'activo'],
    ['nombre' => 'Andres',    'apellido' => 'Morales',    'email' => 'andres.morales@academia.com',     'estatus' => 'activo'],
    ['nombre' => 'Gabriela',  'apellido' => 'Vargas',     'email' => 'gabriela.vargas@academia.com',    'estatus' => 'activo'],
    ['nombre' => 'Fernando',  'apellido' => 'Reyes',      'email' => 'fernando.reyes@academia.com',     'estatus' => 'activo'],
    ['nombre' => 'Daniela',   'apellido' => 'Gutierrez',  'email' => 'daniela.gutierrez@academia.com',  'estatus' => 'activo'],
];
$instResult = $db->instructores->insertMany($instDocs);
$instIds    = $instResult->getInsertedIds();
echo "Instructores insertados: " . count($instIds) . "\n";

$instMap = [];
foreach ($instIds as $i => $oid) {
    $instMap[$instDocs[$i]['nombre']] = ['id' => (string)$oid, 'doc' => $instDocs[$i]];
}

// ── CURSOS ───────────────────────────────────────────────────────────────────
// [categoria, nivel, instructor, nombre, descripcion, precio, duracion_min, fecha_pub, estatus]
$cursosSrc = [
    // Desarrollo Web
    ['Desarrollo Web', 'Principiante', 'Carlos',   'HTML y CSS desde Cero',              'Aprende a construir paginas web con HTML5 y CSS3 modernos.',             249.00,  900, '2024-01-10', 'activo'],
    ['Desarrollo Web', 'Intermedio',   'Carlos',   'JavaScript Moderno ES6+',            'Domina las ultimas caracteristicas de JavaScript con proyectos reales.', 449.00, 1500, '2024-02-15', 'activo'],
    ['Desarrollo Web', 'Avanzado',     'Roberto',  'React con TypeScript',               'Construye aplicaciones escalables con React 18 y TypeScript.',           649.00, 2000, '2024-03-20', 'inactivo'],
    ['Desarrollo Web', 'Experto',      'Ana',      'Arquitectura de Microservicios',      'Disena e implementa sistemas distribuidos con Node.js y Docker.',        899.00, 2800, '2024-04-05', 'activo'],

    // Desarrollo Movil
    ['Desarrollo Movil', 'Principiante', 'Diego',    'Flutter desde Cero',               'Crea tu primera app movil con Flutter y Dart sin experiencia previa.',   349.00, 1200, '2024-01-25', 'activo'],
    ['Desarrollo Movil', 'Intermedio',   'Diego',    'React Native Profesional',         'Desarrolla apps iOS y Android con React Native y Expo.',                  549.00, 1800, '2024-03-10', 'inactivo'],
    ['Desarrollo Movil', 'Avanzado',     'Carlos',   'Android con Kotlin Avanzado',      'Arquitectura MVVM, Jetpack Compose y publicacion en Google Play.',        699.00, 2200, '2024-05-01', 'activo'],

    // Inteligencia Artificial
    ['Inteligencia Artificial', 'Intermedio', 'Miguel',   'Machine Learning con Python',     'Algoritmos de ML, regresion, clasificacion y clustering con Scikit.',  799.00, 2400, '2024-02-01', 'activo'],
    ['Inteligencia Artificial', 'Avanzado',   'Miguel',   'Deep Learning con TensorFlow',    'Redes neuronales, CNNs, RNNs y modelos de lenguaje con TF2.',           999.00, 3200, '2024-03-15', 'activo'],
    ['Inteligencia Artificial', 'Experto',    'Andres',   'LLMs e Ingenieria de Prompts',    'Fine-tuning de modelos, RAG, LangChain y despliegue de LLMs.',         1199.00, 3600, '2024-06-01', 'inactivo'],

    // Ciencia de Datos
    ['Ciencia de Datos', 'Principiante', 'Gabriela', 'Introduccion a la Ciencia de Datos', 'Estadistica basica, Python y primeros pasos en el analisis de datos.',  299.00, 1100, '2024-01-20', 'activo'],
    ['Ciencia de Datos', 'Intermedio',   'Gabriela', 'Analisis de Datos con Pandas',        'Limpieza, transformacion y visualizacion de datos con Python.',          499.00, 1700, '2024-03-05', 'activo'],
    ['Ciencia de Datos', 'Avanzado',     'Miguel',   'Big Data con Spark y Hadoop',         'Procesamiento masivo de datos, arquitecturas Lambda y Kappa.',           799.00, 2600, '2024-05-20', 'inactivo'],

    // Ciberseguridad
    ['Ciberseguridad', 'Principiante', 'Fernando',  'Fundamentos de Ciberseguridad',     'Conceptos basicos de seguridad, amenazas y buenas practicas.',            299.00, 1000, '2024-02-10', 'activo'],
    ['Ciberseguridad', 'Intermedio',   'Fernando',  'Ethical Hacking con Kali Linux',    'Pentesting, escaneo de vulnerabilidades y explotacion etica.',            699.00, 2400, '2024-04-15', 'activo'],
    ['Ciberseguridad', 'Avanzado',     'Andres',    'Seguridad en Aplicaciones Web',     'OWASP Top 10, XSS, SQL Injection, CSRF y tecnicas de defensa.',           849.00, 2800, '2024-06-10', 'inactivo'],

    // Diseno UI/UX
    ['Diseno UI/UX', 'Principiante', 'Sofia',    'Fundamentos de UX Design',           'Investigacion de usuarios, wireframes y principios de usabilidad.',       349.00, 1200, '2024-01-15', 'activo'],
    ['Diseno UI/UX', 'Intermedio',   'Sofia',    'Diseno de Interfaces con Figma',     'Componentes, auto-layout, prototipos interactivos y design systems.',      499.00, 1600, '2024-03-01', 'activo'],
    ['Diseno UI/UX', 'Avanzado',     'Valeria',  'Design Systems Profesionales',       'Crea y documenta sistemas de diseno escalables para equipos.',             699.00, 2000, '2024-05-10', 'activo'],

    // Diseno Grafico
    ['Diseno Grafico', 'Principiante', 'Valeria',  'Diseno Grafico con Adobe Illustrator', 'Vectores, tipografia, composicion y principios del diseno visual.',    399.00, 1300, '2024-02-05', 'activo'],
    ['Diseno Grafico', 'Intermedio',   'Sofia',    'Branding e Identidad Visual',           'Crea marcas solidas: logotipos, paletas, manual de identidad.',        549.00, 1800, '2024-04-20', 'inactivo'],
    ['Diseno Grafico', 'Avanzado',     'Valeria',  'Ilustracion Digital Avanzada',          'Tecnicas de ilustracion profesional con Procreate y Photoshop.',      649.00, 2100, '2024-06-15', 'activo'],

    // Fotografia y Video
    ['Fotografia y Video', 'Principiante', 'Laura',    'Fotografia para Principiantes',     'Manejo de camara, composicion, luz natural y primeras ediciones.',     299.00, 1000, '2024-01-30', 'activo'],
    ['Fotografia y Video', 'Intermedio',   'Laura',    'Edicion de Video con Premiere Pro',  'Corte, color grading, efectos y exportacion profesional.',             499.00, 1600, '2024-03-25', 'inactivo'],
    ['Fotografia y Video', 'Avanzado',     'Diego',    'Produccion Audiovisual Profesional',  'Direccion, iluminacion, sonido y postproduccion de alto nivel.',      799.00, 2500, '2024-05-15', 'activo'],

    // Musica y Produccion
    ['Musica y Produccion', 'Principiante', 'Andres',   'Produccion Musical desde Cero',      'DAWs, teoria musical, samples y tu primera cancion terminada.',      349.00, 1200, '2024-02-20', 'inactivo'],
    ['Musica y Produccion', 'Intermedio',   'Andres',   'Mezcla y Masterizacion',              'Tecnicas de mixing profesional, EQ, compresion y mastering.',       549.00, 1800, '2024-04-10', 'inactivo'],
    ['Musica y Produccion', 'Avanzado',     'Fernando', 'Composicion para Cine y Videojuegos', 'Musica orquestal, temas adaptivos y sincronizacion audiovisual.',   799.00, 2600, '2024-06-05', 'inactivo'],

    // Marketing Digital
    ['Marketing Digital', 'Principiante', 'Ana',      'Marketing Digital desde Cero',     'Fundamentos del marketing online, redes sociales y estrategia.',         249.00,  900, '2024-01-12', 'activo'],
    ['Marketing Digital', 'Intermedio',   'Ana',      'SEO Avanzado y Posicionamiento',   'Tecnicas on-page, off-page, link building y auditoria SEO.',             499.00, 1600, '2024-03-08', 'activo'],
    ['Marketing Digital', 'Avanzado',     'Gabriela', 'Performance Marketing y Ads',      'Google Ads, Meta Ads, analisis de metricas y optimizacion de campanas.', 699.00, 2200, '2024-05-25', 'inactivo'],

    // Negocios y Emprendimiento
    ['Negocios y Emprendimiento', 'Principiante', 'Roberto',  'Como Crear tu Startup',           'Validacion de ideas, MVP, modelo de negocio y primeros clientes.',    299.00, 1100, '2024-02-08', 'activo'],
    ['Negocios y Emprendimiento', 'Intermedio',   'Roberto',  'Liderazgo y Gestion de Equipos',  'Comunicacion efectiva, delegacion, feedback y cultura organizacional.',449.00, 1500, '2024-04-18', 'activo'],
    ['Negocios y Emprendimiento', 'Avanzado',     'Daniela',  'Estrategia Empresarial Avanzada', 'Analisis competitivo, expansion, fusiones y estrategia corporativa.',  699.00, 2000, '2024-06-20', 'inactivo'],

    // Finanzas Personales
    ['Finanzas Personales', 'Principiante', 'Daniela',  'Finanzas Personales 101',         'Presupuesto, ahorro, deudas y primeros pasos para invertir.',              199.00,  800, '2024-01-22', 'activo'],
    ['Finanzas Personales', 'Intermedio',   'Daniela',  'Inversion en Bolsa para Todos',   'Acciones, ETFs, diversificacion y como analizar empresas.',                449.00, 1500, '2024-03-30', 'activo'],
    ['Finanzas Personales', 'Avanzado',     'Roberto',  'Criptomonedas y Blockchain',      'Bitcoin, Ethereum, DeFi, NFTs y estrategias de inversion en crypto.',     649.00, 2000, '2024-05-28', 'inactivo'],

    // Idiomas
    ['Idiomas', 'Principiante', 'Laura',    'Ingles desde Cero',         'Pronunciacion, vocabulario basico, gramatica y conversacion inicial.',   199.00,  900, '2024-01-18', 'inactivo'],
    ['Idiomas', 'Intermedio',   'Gabriela', 'Ingles de Negocios',        'Vocabulario corporativo, presentaciones, emails y negociaciones.',        349.00, 1300, '2024-03-22', 'inactivo'],
    ['Idiomas', 'Principiante', 'Valeria',  'Japones para Principiantes','Hiragana, katakana, vocabulario esencial y frases cotidianas.',           299.00, 1100, '2024-05-05', 'inactivo'],

    // Fitness y Salud
    ['Fitness y Salud', 'Principiante', 'Laura',  'Entrenamiento en Casa sin Equipo', 'Rutinas efectivas para perder peso y ganar fuerza desde casa.',           149.00,  600, '2024-02-14', 'activo'],
    ['Fitness y Salud', 'Intermedio',   'Sofia',  'Yoga y Meditacion',                'Posturas, respiracion, mindfulness y reduccion del estres.',              249.00,  900, '2024-04-02', 'activo'],
    ['Fitness y Salud', 'Avanzado',     'Laura',  'Nutricion Deportiva Avanzada',     'Macronutrientes, suplementacion, periodizacion nutricional y rendimiento.',449.00, 1500, '2024-06-12', 'inactivo'],

    // Cocina y Gastronomia
    ['Cocina y Gastronomia', 'Principiante', 'Daniela', 'Cocina Basica para el Dia a Dia',     'Tecnicas esenciales, cortes, fondos y recetas practicas y deliciosas.',  199.00,  700, '2024-01-28', 'activo'],
    ['Cocina y Gastronomia', 'Intermedio',   'Ana',     'Reposteria Francesa',                  'Croissants, macarons, tarta tatin y tecnicas de la pasteleria clasica.', 349.00, 1200, '2024-03-18', 'activo'],
    ['Cocina y Gastronomia', 'Avanzado',     'Daniela', 'Alta Cocina y Gastronomia Molecular',  'Esferificacion, geles, espumas y tecnicas de restaurantes estrella.',    699.00, 2000, '2024-05-22', 'inactivo'],

    // Desarrollo Personal
    ['Desarrollo Personal', 'Principiante', 'Roberto',  'Habitos Atomicos en Practica',      'Construye y mantiene habitos poderosos usando ciencia del comportamiento.',199.00,  700, '2024-02-02', 'activo'],
    ['Desarrollo Personal', 'Intermedio',   'Gabriela', 'Productividad y Gestion del Tiempo', 'GTD, Pomodoro, time blocking y sistemas de organizacion personal.',        299.00, 1000, '2024-04-08', 'activo'],
    ['Desarrollo Personal', 'Avanzado',     'Ana',      'Inteligencia Emocional',             'Autoconciencia, empatia, manejo de conflictos y relaciones saludables.',   399.00, 1300, '2024-06-18', 'inactivo'],

    // Videojuegos
    ['Videojuegos', 'Principiante', 'Diego',    'Unity desde Cero',                  'Crea tu primer videojuego 2D con Unity y C# paso a paso.',               349.00, 1200, '2024-01-16', 'activo'],
    ['Videojuegos', 'Intermedio',   'Diego',    'Desarrollo de Juegos 3D con Unity', 'Fisicas, animaciones, IA de enemigos y mecanicas de juego avanzadas.',   599.00, 2000, '2024-03-28', 'activo'],
    ['Videojuegos', 'Avanzado',     'Fernando', 'Unreal Engine 5 Profesional',       'Nanite, Lumen, Blueprints avanzados y publicacion en Steam.',             899.00, 3000, '2024-06-08', 'inactivo'],

    // DevOps y Cloud
    ['DevOps y Cloud', 'Principiante', 'Andres',   'Docker y Contenedores',      'Imagenes, contenedores, docker-compose y primeros despliegues.',           349.00, 1200, '2024-02-18', 'inactivo'],
    ['DevOps y Cloud', 'Intermedio',   'Andres',   'Kubernetes en Produccion',   'Pods, deployments, servicios, Helm charts y escalado automatico.',         699.00, 2400, '2024-04-25', 'inactivo'],
    ['DevOps y Cloud', 'Avanzado',     'Carlos',   'AWS Solutions Architect',    'EC2, S3, RDS, Lambda, CloudFormation y preparacion para certificacion.',   999.00, 3200, '2024-06-28', 'inactivo'],

    // Bases de Datos
    ['Bases de Datos', 'Principiante', 'Gabriela', 'SQL desde Cero',                  'Consultas, joins, subconsultas y diseno de bases de datos relacionales.', 249.00,  900, '2024-01-14', 'activo'],
    ['Bases de Datos', 'Intermedio',   'Miguel',   'MongoDB para Desarrolladores',     'Modelado de documentos, agregaciones, indices y rendimiento en MongoDB.',  449.00, 1500, '2024-03-12', 'activo'],
    ['Bases de Datos', 'Avanzado',     'Andres',   'Administracion Avanzada de BD',    'Optimizacion, replicacion, sharding, backups y alta disponibilidad.',     749.00, 2500, '2024-05-18', 'inactivo'],

    // Arquitectura y Diseno 3D
    ['Arquitectura y Diseno 3D', 'Principiante', 'Fernando', 'Blender desde Cero',              'Modelado 3D, materiales, iluminacion y primeros renders en Blender.',    299.00, 1100, '2024-02-25', 'activo'],
    ['Arquitectura y Diseno 3D', 'Intermedio',   'Valeria',  'Visualizacion Arquitectonica',     'Renders fotorrealistas de interiores y exteriores con 3ds Max y V-Ray.', 599.00, 2000, '2024-04-30', 'activo'],
    ['Arquitectura y Diseno 3D', 'Avanzado',     'Fernando', 'Animacion y VFX con Blender',      'Rigging, animacion de personajes, simulaciones y efectos visuales.',      799.00, 2800, '2024-06-25', 'inactivo'],
];

$cursoDocs = [];
foreach ($cursosSrc as [$catNombre, $nivel, $instNombre, $nombre, $desc, $precio, $durMin, $fecha, $estatus]) {
    $inst = $instMap[$instNombre];
    $cursoDocs[] = [
        'categoria'         => ['id' => $catMap[$catNombre], 'nombre' => $catNombre],
        'nivel'             => $nivel,
        'instructor_id'     => $inst['id'],
        'instructor'        => [
            'nombre'   => $inst['doc']['nombre'],
            'apellido' => $inst['doc']['apellido'],
            'email'    => $inst['doc']['email'],
        ],
        'nombre'            => $nombre,
        'descripcion'       => $desc,
        'precio'            => $precio,
        'duracion_minutos'  => $durMin,
        'fecha_publicacion' => $fecha,
        'estatus'           => $estatus,
        'fecha_creacion'    => new MongoDB\BSON\UTCDateTime(),
    ];
}
$db->cursos->insertMany($cursoDocs);
echo "Cursos insertados: " . count($cursoDocs) . "\n";

// ── ÍNDICES ──────────────────────────────────────────────────────────────────
$db->categorias->createIndex(['nombre' => 1], ['unique' => true]);
$db->cursos->createIndex(['categoria.id' => 1]);
$db->cursos->createIndex(['instructor_id' => 1]);
$db->cursos->createIndex(['estatus' => 1]);
$db->cursos->createIndex(['nombre' => 'text']);
echo "Indices creados.\n";

echo "\n✅ Seed completado. Base de datos 'cursos' lista en MongoDB.\n";