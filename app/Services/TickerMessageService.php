<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TickerMessageService
{
    private $cacheExpiration = 1800; // 30 minutos

    /**
     * Obtiene las estadísticas de portátiles por jornada del día actual
     */
    public function getPortatilesPorJornada(): array
    {
        $today = Carbon::today();
        $resultados = DB::table('asistencias as a')
            ->join('users as u', 'a.user_id', '=', 'u.id')
            ->join('devices as d', 'd.user_id', '=', 'u.id')
            ->join('jornadas as j', 'u.jornada_id', '=', 'j.id')
            ->select('j.nombre as jornada', DB::raw('COUNT(DISTINCT u.id) as total_portatiles'))
            ->where('a.fecha_hora', '>=', $today)
            ->where('a.fecha_hora', '<', $today->copy()->addDay())
            ->groupBy('j.nombre')
            ->get();
            
        return json_decode(json_encode($resultados), true);
    }

    /**
     * Obtiene el primer aprendiz en llegar por jornada
     */
    public function getPrimerosEnLlegar(): array
    {
        $today = Carbon::today();
        $resultados = DB::table('asistencias as a')
            ->join('users as u', 'a.user_id', '=', 'u.id')
            ->join('jornadas as j', 'u.jornada_id', '=', 'j.id')
            ->join('programa_formacion as p', 'u.id', '=', 'p.user_id')
            ->select(
                'j.nombre as jornada',
                'u.nombres_completos',
                'a.fecha_hora',
                'p.nombre_programa',
                'p.numero_ficha'
            )
            ->where('a.tipo', 'entrada')
            ->where('a.fecha_hora', '>=', $today)
            ->where('a.fecha_hora', '<', $today->copy()->addDay())
            ->whereRaw('(j.nombre, a.fecha_hora) IN (
                SELECT j2.nombre, MIN(a2.fecha_hora)
                FROM asistencias a2
                JOIN users u2 ON a2.user_id = u2.id
                JOIN jornadas j2 ON u2.jornada_id = j2.id
                WHERE a2.tipo = "entrada"
                AND a2.fecha_hora >= ?
                AND a2.fecha_hora < ?
                GROUP BY j2.nombre
            )', [$today, $today->copy()->addDay()])
            ->get();
            
        return json_decode(json_encode($resultados), true);
    }

    /**
     * Obtiene estadísticas de portátiles por marca
     */
    public function getPortatilesPorMarca(): array
    {
        return Cache::remember('portatiles_por_marca', $this->cacheExpiration, function () {
            $resultados = DB::table('devices')
                ->select('marca', DB::raw('COUNT(*) as total'))
                ->groupBy('marca')
                ->having('total', '>', 1)
                ->orderBy('total', 'desc')
                ->get();
                
            return json_decode(json_encode($resultados), true);
        });
    }

    /**
     * Obtiene datos de los programas de formación
     */
    public function getDatosProgramas(): array
    {
        return Cache::remember('datos_programas', $this->cacheExpiration, function () {
            $resultados = DB::table('programa_formacion')
                ->select('nombre_programa', 'numero_ficha', 'numero_ambiente')
                ->get();
                
            return json_decode(json_encode($resultados), true);
        });
    }

    /**
     * Obtiene los nuevos aprendices registrados en las últimas 24 horas
     */
    public function getNuevosAprendices(): array
    {
        return Cache::remember('nuevos_aprendices', 300, function () { // 5 minutos
            $resultados = DB::table('users as u')
                ->join('programa_formacion as p', 'u.id', '=', 'p.user_id')
                ->join('jornadas as j', 'u.jornada_id', '=', 'j.id')
                ->select(
                    'u.nombres_completos',
                    'p.nombre_programa',
                    'p.numero_ficha',
                    'j.nombre as jornada',
                    'u.created_at'
                )
                ->where('u.rol', 'aprendiz')
                ->where('u.created_at', '>=', now()->subDay())
                ->orderBy('u.created_at', 'desc')
                ->get();
                
            return json_decode(json_encode($resultados), true);
        });
    }

    /**
     * Obtiene las últimas asistencias registradas
     */
    public function getUltimasAsistencias(int $minutos = 15): array
    {
        return Cache::remember('ultimas_asistencias', 60, function () use ($minutos) { // 1 minuto
            $resultados = DB::table('asistencias as a')
                ->join('users as u', 'a.user_id', '=', 'u.id')
                ->join('programa_formacion as p', 'u.id', '=', 'p.user_id')
                ->join('jornadas as j', 'u.jornada_id', '=', 'j.id')
                ->where('a.fecha_hora', '>=', Carbon::now()->subMinutes($minutos))
                ->select(
                    'u.nombres_completos',
                    'p.nombre_programa',
                    'p.numero_ficha',
                    'j.nombre as jornada',
                    'a.fecha_hora',
                    'a.tipo'
                )
                ->orderBy('a.fecha_hora', 'desc')
                ->get();
                
            return json_decode(json_encode($resultados), true);
        });
    }

    /**
     * Genera mensajes personalizados para asistencias
     */
    private function generarMensajesAsistencias(array $asistencias): array
    {
        $mensajes = [];
        foreach ($asistencias as $asistencia) {
            $horaFormateada = Carbon::parse($asistencia['fecha_hora'])->format('h:i A');
            
            if ($asistencia['tipo'] === 'entrada') {
                $mensajes[] = sprintf(
                    "✅ %s de la ficha %s ha llegado a las %s",
                    $asistencia['nombres_completos'],
                    $asistencia['numero_ficha'],
                    $horaFormateada
                );
            } else {
                $mensajes[] = sprintf(
                    "👋 %s de la ficha %s se ha retirado a las %s",
                    $asistencia['nombres_completos'],
                    $asistencia['numero_ficha'],
                    $horaFormateada
                );
            }
        }
        return $mensajes;
    }
    
    /**
     * Genera mensajes sobre el SENA y su identidad institucional
     */
    public function generarMensajesInstitucionales(): array
    {
        return [
            "🇨🇴 El SENA es patrimonio de todos los colombianos",
            "📣 SENA: Formación gratuita de calidad para el desarrollo del país",
            "🌱 SENA: Formamos el talento humano que transforma a Colombia",
            "🤝 El SENA trabaja por la inclusión social y el desarrollo sostenible",
            "⚒️ Fomentamos la productividad y competitividad del sector productivo",
            "🧩 SENA contribuye a la transformación social y económica del país",
            "🏆 El SENA: 67 años formando profesionales integrales en Colombia",
            "📊 Somos la institución de formación técnica más querida por los colombianos",
            "🛠️ Formamos el talento humano que requieren las empresas colombianas",
            "🌎 Promovemos el desarrollo tecnológico para la innovación y competitividad",
            "🧠 Fomentamos el pensamiento crítico y las competencias laborales",
            "🚩 Regional Caquetá: Formando profesionales para el desarrollo amazónico",
            "📚 El SENA ofrece más de 400 programas de formación técnica y tecnológica",
            "💼 Facilitamos la incorporación de los aprendices al mundo laboral",
            "🔄 Adaptamos nuestros programas a las necesidades del sector productivo",
            "👩‍🎓 Con el SENA, el conocimiento se transforma en oportunidades",
            "🌿 CampeSENA: Impulsamos el desarrollo del campo colombiano",
            "🧪 Tecnoacademias: Inspiramos a jóvenes con ciencia e innovación",
            "💻 SENA Digital: Formación virtual para todos los colombianos",
            "🔍 Certificamos tu experiencia laboral con validez nacional",
            "🚀 SENAInnova: Impulsamos proyectos de ciencia, tecnología e innovación",
            "🌐 33 regionales y 117 centros de formación en todo el país",
            "🔧 Desarrollamos capacidades para la Cuarta Revolución Industrial",
            "🤖 WorldSkills: Competencias de talla mundial para nuestros aprendices"
        ];
    }
    
    /**
     * Genera mensajes motivacionales para aprendices
     */
    public function generarMensajesMotivacionales(): array
    {
        return [
            "✨ La perseverancia es la clave del éxito en tu formación profesional",
            "🚀 El futuro pertenece a quienes creen en la belleza de sus sueños",
            "💡 Cada problema es una oportunidad disfrazada de desafío",
            "📚 El aprendizaje es un tesoro que seguirá a su dueño por todas partes",
            "🔧 Las habilidades prácticas son tan importantes como el conocimiento teórico",
            "🌟 Tu actitud determina tu dirección. ¡Mantén una actitud positiva!",
            "🎯 Establece metas claras y trabaja con disciplina para alcanzarlas",
            "🧠 El cerebro es como un músculo: cuanto más lo ejercitas, más crece",
            "💪 El éxito no es casualidad, es el resultado de la dedicación diaria",
            "🌈 Tu futuro se construye con las decisiones que tomas hoy",
            "⏰ La puntualidad refleja tu compromiso con tu formación y con los demás",
            "🌱 Cada nuevo aprendizaje es una semilla para tu crecimiento profesional",
            "🔍 La curiosidad es el motor del aprendizaje permanente",
            "🤝 El trabajo en equipo multiplica resultados y divide esfuerzos",
            "📱 La tecnología es una herramienta poderosa cuando se usa con propósito",
            "🏆 No hay ascensor hacia el éxito, hay que tomar las escaleras",
            "🧗‍♀️ Los obstáculos son esas cosas atemorizantes que ves cuando apartas la vista de tu meta",
            "🔄 El aprendizaje constante es la clave para adaptarse a un mundo cambiante",
            "🌊 La educación es el arma más poderosa para cambiar el mundo",
            "🗣️ Las habilidades de comunicación son fundamentales en cualquier profesión",
            "🦋 La transformación personal comienza con pequeños cambios diarios",
            "⚡ La motivación te pone en marcha, pero el hábito te mantiene en el camino",
            "📈 Celebra cada logro, por pequeño que sea, en tu camino hacia tus metas",
            "🎭 Sal de tu zona de confort: ahí es donde ocurre la verdadera magia",
            "💎 La excelencia no es un acto, sino un hábito en tu formación"
        ];
    }
    
    /**
     * Genera mensajes sobre tecnología e innovación
     */
    public function generarMensajesTecnologia(): array
    {
        return [
            "💻 SENA Digital: formación virtual gratuita en tecnología e innovación",
            "🔌 TecnoParques: espacios de innovación tecnológica abiertos a todos",
            "📊 El SENA forma talento digital para la transformación de Colombia",
            "🛠️ Desarrollamos competencias técnicas para la Industria 4.0",
            "🚗 El SENA forma técnicos en nuevas tecnologías de movilidad sostenible",
            "🌐 Nuestros programas TIC desarrollan talento para la economía digital",
            "🤖 Robótica, IA y automatización: campos de formación para el futuro",
            "📲 El SENA impulsa el desarrollo de aplicaciones móviles innovadoras",
            "🧩 La programación y el desarrollo de software son prioridades formativas",
            "🔐 Formamos especialistas en ciberseguridad para proteger la información",
            "☁️ El SENA desarrolla competencias en computación en la nube",
            "📱 Aplicamos tecnologías móviles para mejorar procesos productivos",
            "🧪 Las Tecnoacademias SENA despiertan vocaciones científicas en jóvenes",
            "🔎 El análisis de datos es fundamental en nuestros programas tecnológicos",
            "🔄 El SENA apoya la transformación digital de las empresas colombianas",
            "🧬 Las biotecnologías son parte de nuestra oferta formativa innovadora",
            "🌐 El Internet de las Cosas revoluciona la formación técnica SENA",
            "⚙️ La fabricación digital y la impresión 3D son competencias del futuro",
            "📱 SENNOVA impulsa proyectos de investigación aplicada e innovación",
            "🚀 Las tecnologías emergentes son prioridad en la formación SENA"
        ];
    }
    
    /**
     * Genera mensajes sobre formación profesional y educación
     */
    public function generarMensajesFormacion(): array
    {
        return [
            "📝 La formación por proyectos es el modelo pedagógico del SENA",
            "📗 La formación técnica del SENA impulsa el desarrollo industrial del país",
            "🎓 El SENA ofrece formación profesional integral totalmente gratuita",
            "🏭 El SENA fortalece la formación dual para conectar teoría y práctica",
            "👨‍🏫 Nuestros instructores son expertos en sus áreas con experiencia real",
            "🔄 La formación continua te permite adaptarte a un mercado laboral cambiante",
            "💭 Desarrollamos pensamiento crítico para resolver problemas complejos",
            "📈 Las competencias técnicas SENA aumentan tu empleabilidad",
            "🧘‍♀️ La formación SENA incluye desarrollo integral de la persona",
            "🎨 Economía Naranja: el SENA impulsa las industrias creativas y culturales",
            "🌍 La sostenibilidad es un eje transversal en nuestros programas formativos",
            "💰 La educación financiera es parte de la formación emprendedora del SENA",
            "🗣️ Las habilidades blandas son fundamentales en nuestra formación integral",
            "🧗‍♂️ Formamos para superar retos con competencias técnicas y personales",
            "📚 Nuestras bibliotecas apoyan el proceso formativo con recursos especializados",
            "🤔 El aprendizaje basado en problemas desarrolla tu capacidad analítica",
            "🔄 La metodología SENA se enfoca en aprender haciendo",
            "💻 La alfabetización digital es fundamental en todos nuestros programas",
            "📊 La formación por competencias te prepara para resultados medibles",
            "🌐 El bilingüismo multiplica tus oportunidades laborales y profesionales"
        ];
    }
    
    /**
     * Genera mensajes sobre hábitos y desarrollo personal
     */
    public function generarMensajesHabitos(): array
    {
        return [
            "⏰ La puntualidad es un valor esencial para los aprendices SENA",
            "📋 Planificar tus actividades formativas mejora tu desempeño académico",
            "💧 El bienestar físico es parte integral de la formación SENA",
            "💤 El descanso adecuado mejora tu capacidad de aprendizaje",
            "🍎 La alimentación saludable te da energía para tu formación práctica",
            "📵 El uso responsable de dispositivos móviles mejora la concentración",
            "🧘 El bienestar psicosocial es fundamental para tu desarrollo integral",
            "📝 Tomar apuntes durante la formación refuerza tu aprendizaje",
            "👥 El trabajo colaborativo es una competencia clave en el SENA",
            "📚 El repaso constante de los temas formativos consolida conocimientos",
            "🏃‍♀️ El ejercicio regular mejora tu rendimiento académico y laboral",
            "🌿 Las pausas activas son importantes durante largas jornadas de formación",
            "📅 Establecer rutinas de estudio te ayuda a optimizar tu tiempo",
            "🧠 Aplicar técnicas de concentración mejora tu aprendizaje práctico",
            "🗂️ Mantener organizados tus materiales de estudio facilita tu formación",
            "💻 Respaldar tus proyectos y trabajos es una buena práctica profesional",
            "📱 Usar responsablemente la tecnología te hace más productivo",
            "🚶‍♂️ Alternar actividades teóricas y prácticas mejora la asimilación",
            "🗣️ Compartir conocimientos con otros aprendices refuerza tu aprendizaje",
            "⚖️ El equilibrio entre vida personal y formación es clave para tu éxito"
        ];
    }
    
    /**
     * Genera mensajes sobre éxito profesional y empleabilidad
     */
    public function generarMensajesEmpleabilidad(): array
    {
        return [
            "📋 La Agencia Pública de Empleo del SENA conecta talento con empresas",
            "👥 El SENA tiene la red de empleabilidad más grande del país",
            "📱 Descarga la app SENA Empleo para encontrar oportunidades laborales",
            "🎯 El SENA impulsa la formación en sectores con alta demanda laboral",
            "🤝 El contrato de aprendizaje facilita tu primera experiencia profesional",
            "📈 El Observatorio Laboral SENA analiza tendencias del mercado de trabajo",
            "💡 El emprendimiento SENA es una alternativa para crear tu propio empleo",
            "🔍 Las empresas valoran altamente a los aprendices del SENA",
            "🌐 Desarrollamos competencias para la empleabilidad global",
            "🚀 El Fondo Emprender financia nuevas iniciativas empresariales",
            "📊 Las certificaciones SENA son reconocidas por el sector productivo",
            "👔 Preparamos profesionales integrales con valores y competencias técnicas",
            "🔄 Fortalecemos la capacidad de adaptación a entornos laborales cambiantes",
            "📢 La comunicación efectiva es clave en el mundo laboral actual",
            "🧩 Identificamos tus fortalezas para potenciar tu perfil profesional",
            "📝 El SENA te ayuda a construir un portafolio profesional sólido",
            "🤔 La inteligencia emocional mejora tus relaciones laborales",
            "🗣️ Desarrollamos habilidades de negociación y resolución de conflictos",
            "🔄 El SENA te prepara para la movilidad laboral y el aprendizaje permanente",
            "🌱 Cultivamos el crecimiento profesional con herramientas y competencias"
        ];
    }
    
    /**
     * Genera datos curiosos e interesantes
     */
    public function generarDatosCuriosos(): array
    {
        return [
            "💡 El SENA fue fundado en 1957 y ha formado a millones de colombianos",
            "🏫 El SENA cuenta con 33 regionales y 117 centros de formación en todo el país",
            "🌐 La plataforma SENA Digital ofrece más de 400 programas de formación virtual gratuitos",
            "🧪 Las Tecnoacademias del SENA inspiran a jóvenes de zonas rurales con ciencia e innovación",
            "🏅 Colombia ha ganado medallas en WorldSkills, las olimpiadas mundiales de habilidades técnicas",
            "🌱 El programa SENA Emprende Rural ha beneficiado a miles de familias campesinas",
            "📱 El SENA cuenta con aplicaciones móviles para facilitar el acceso a sus servicios",
            "🤖 Las aulas móviles del SENA llevan formación especializada a regiones apartadas",
            "🔍 La Agencia Pública de Empleo del SENA es la bolsa de empleo más grande del país",
            "📊 El Observatorio Laboral del SENA analiza las tendencias de empleo en Colombia",
            "💼 El contrato de aprendizaje SENA beneficia anualmente a miles de empresas colombianas",
            "📚 Las bibliotecas del SENA cuentan con más de un millón de recursos bibliográficos",
            "🔬 Los tecnoparques del SENA son espacios de innovación abiertos a todos los colombianos",
            "🧠 El SENA certifica competencias laborales adquiridas a través de la experiencia",
            "👩‍🍳 Empresas como Crepes & Waffles trabajan con el SENA para certificar el conocimiento de sus empleados",
            "🚜 La inseminación artificial es una de las técnicas sostenibles enseñadas por CampeSENA",
            "🌍 El SENA tiene convenios internacionales para el intercambio de aprendices y conocimientos",
            "💻 El programa SENA Innova impulsa proyectos de transformación digital empresarial",
            "🧗‍♂️ El SENA ofrece formación especializada en trabajo seguro en alturas",
            "🌟 Las mesas sectoriales del SENA definen estándares de competencia laboral con la industria"
        ];
    }

    /**
     * Obtiene las formaciones por jornada
     */
    public function getFormacionesPorJornada(): array
    {
        return Cache::remember('formaciones_por_jornada', $this->cacheExpiration, function () {
            $resultados = DB::table('programa_formacion as p')
                ->join('users as u', 'p.user_id', '=', 'u.id')
                ->join('jornadas as j', 'u.jornada_id', '=', 'j.id')
                ->select('j.nombre as jornada', DB::raw('COUNT(DISTINCT p.nombre_programa) as total_programas'))
                ->groupBy('j.nombre')
                ->get();
                
            return json_decode(json_encode($resultados), true);
        });
    }

    /**
     * Obtiene el total de aprendices con portátiles personales
     */
    public function getTotalAprendicesConPortatiles(): int
    {
        return Cache::remember('total_aprendices_portatiles', $this->cacheExpiration, function () {
            return DB::table('users as u')
                ->join('devices as d', 'd.user_id', '=', 'u.id')
                ->where('u.rol', 'aprendiz')
                ->count(DB::raw('DISTINCT u.id'));
        });
    }

    /**
     * Obtiene datos específicos de los programas de formación
     */
    public function getDatosCuriososProgramas(): array
    {
        return Cache::remember('datos_curiosos_programas', $this->cacheExpiration, function () {
            $programas = DB::table('programa_formacion as p')
                ->select('p.nombre_programa', 'p.numero_ficha', 
                    DB::raw('COUNT(DISTINCT u.id) as total_aprendices'))
                ->join('users as u', 'p.user_id', '=', 'u.id')
                ->where('u.rol', 'aprendiz')
                ->groupBy('p.nombre_programa', 'p.numero_ficha')
                ->having(DB::raw('COUNT(DISTINCT u.id)'), '>', 0)
                ->get();
                
            $programasArray = json_decode(json_encode($programas), true);
            $mensajes = [];
            
            foreach ($programasArray as $programa) {
                $mensajes[] = sprintf(
                    "👨‍💻 El programa %s (Ficha %s) cuenta con %d aprendices registrados",
                    $programa['nombre_programa'],
                    $programa['numero_ficha'],
                    $programa['total_aprendices']
                );
                
                // Solo para programas de tecnología/informática
                if (str_contains(strtolower($programa['nombre_programa']), 'software') || 
                    str_contains(strtolower($programa['nombre_programa']), 'sistemas') ||
                    str_contains(strtolower($programa['nombre_programa']), 'tecnología') ||
                    str_contains(strtolower($programa['nombre_programa']), 'informática')) {
                    $mensajes[] = sprintf(
                        "💻 Los aprendices de %s están desarrollando competencias para la industria 4.0",
                        $programa['nombre_programa']
                    );
                }
                
                // Para programas administrativos
                if (str_contains(strtolower($programa['nombre_programa']), 'administra') ||
                    str_contains(strtolower($programa['nombre_programa']), 'contable') ||
                    str_contains(strtolower($programa['nombre_programa']), 'negocio')) {
                    $mensajes[] = sprintf(
                        "📊 El programa %s forma profesionales para la gestión empresarial",
                        $programa['nombre_programa']
                    );
                }
                
                // Para programas agrícolas
                if (str_contains(strtolower($programa['nombre_programa']), 'agro') ||
                    str_contains(strtolower($programa['nombre_programa']), 'agrícola') ||
                    str_contains(strtolower($programa['nombre_programa']), 'ambiental') ||
                    str_contains(strtolower($programa['nombre_programa']), 'forestal')) {
                    $mensajes[] = sprintf(
                        "🌱 Los aprendices de %s contribuyen al desarrollo sostenible de Colombia",
                        $programa['nombre_programa']
                    );
                }
            }
            
            return $mensajes;
        });
    }

    /**
     * Genera mensajes sobre formaciones por jornada
     */
    private function generarMensajesFormacionesPorJornada(): array
    {
        $mensajes = [];
        try {
            $formacionesPorJornada = $this->getFormacionesPorJornada();
            foreach ($formacionesPorJornada as $formacion) {
                $mensajes[] = sprintf(
                    "📚 La jornada de %s cuenta con %d programas de formación diferentes",
                    $formacion['jornada'],
                    $formacion['total_programas']
                );
                
                $mensajes[] = sprintf(
                    "🎓 En %s, el SENA ofrece %d programas formativos distintos",
                    $formacion['jornada'],
                    $formacion['total_programas']
                );
            }
        } catch (\Exception $e) {
            Log::error('Error generando mensajes de formaciones por jornada:', ['error' => $e->getMessage()]);
        }
        
        return $mensajes;
    }

    /**
     * Genera mensajes sobre el total de aprendices con portátiles
     */
    private function generarMensajesTotalPortatiles(): array
    {
        $mensajes = [];
        try {
            $totalAprendices = $this->getTotalAprendicesConPortatiles();
            
            $mensajes[] = sprintf(
                "💻 Actualmente hay %d aprendices registrados con portátil personal",
                $totalAprendices
            );
            
            $mensajes[] = sprintf(
                "📱 El SENA Regional Caquetá cuenta con %d aprendices usando sus propios equipos",
                $totalAprendices
            );
            
            $mensajes[] = sprintf(
                "🔌 %d aprendices utilizan su portátil personal para su formación en el SENA",
                $totalAprendices
            );
        } catch (\Exception $e) {
            Log::error('Error generando mensajes de total de portátiles:', ['error' => $e->getMessage()]);
        }
        
        return $mensajes;
    }

    /**
     * Genera todos los mensajes para el ticker
     */
    public function getMensajes(): array
    {
        // Mensajes base que siempre se incluirán
        $mensajes = [
            "👋 ¡Bienvenidos al SENA!",
            "💻 Sistema de Control de entradas del SENA Regional Caquetá",
            "📣 Formamos el talento humano que transforma a Colombia"
        ];
        
        // Intentar obtener cada tipo de mensaje independientemente
        try {
            $portatilesPorJornada = $this->getPortatilesPorJornada();
            foreach ($portatilesPorJornada as $dato) {
                $mensajes[] = sprintf(
                    "📱 En la jornada de la %s hay %d aprendices con portatil personal", 
                    $dato['jornada'], 
                    $dato['total_portatiles']
                );
            }
        } catch (\Exception $e) {
            Log::error('Error obteniendo portátiles por jornada:', ['error' => $e->getMessage()]);
        }

        try {
            $primerosEnLlegar = $this->getPrimerosEnLlegar();
            foreach ($primerosEnLlegar as $primero) {
                $hora = Carbon::parse($primero['fecha_hora'])->format('h:i A');
                $mensajes[] = sprintf(
                    "🥇 %s fue el primero en llegar a su formación a las %s", 
                    $primero['nombres_completos'],
                    $hora
                );
            }
        } catch (\Exception $e) {
            Log::error('Error obteniendo primeros en llegar:', ['error' => $e->getMessage()]);
        }

        try {
            $portatilesPorMarca = $this->getPortatilesPorMarca();
            foreach ($portatilesPorMarca as $marca) {
                $mensajes[] = sprintf(
                    "💻 %d aprendices utilizan equipos %s", 
                    $marca['total'],
                    $marca['marca']
                );
            }
        } catch (\Exception $e) {
            Log::error('Error obteniendo portátiles por marca:', ['error' => $e->getMessage()]);
        }

        try {
            $programas = $this->getDatosProgramas();
            foreach ($programas as $programa) {
                $mensajes[] = sprintf(
                    "📚 Justo ahora en el Ambiente %s estan los de %s - Ficha %s", 
                    $programa['numero_ambiente'],
                    $programa['nombre_programa'],
                    $programa['numero_ficha']
                );
            }
        } catch (\Exception $e) {
            Log::error('Error obteniendo datos de programas:', ['error' => $e->getMessage()]);
        }

        try {
            $nuevosAprendices = $this->getNuevosAprendices();
            foreach ($nuevosAprendices as $aprendiz) {
                $mensajes[] = sprintf(
                    "🎉 Damos la bienvenida a %s al programa %s", 
                    $aprendiz['nombres_completos'],
                    $aprendiz['nombre_programa']
                );
            }
        } catch (\Exception $e) {
            Log::error('Error obteniendo nuevos aprendices:', ['error' => $e->getMessage()]);
        }

        try {
            $ultimasAsistencias = $this->getUltimasAsistencias(30);
            $mensajesAsistencias = $this->generarMensajesAsistencias($ultimasAsistencias);
            $mensajes = array_merge($mensajes, $mensajesAsistencias);
        } catch (\Exception $e) {
            Log::error('Error obteniendo últimas asistencias:', ['error' => $e->getMessage()]);
        }
        
        // Agregar mensajes con datos de formaciones por jornada
        $mensajesFormacionesJornada = $this->generarMensajesFormacionesPorJornada();
        $mensajes = array_merge($mensajes, $mensajesFormacionesJornada);
        
        // Agregar mensajes con datos de total de aprendices con portátiles
        $mensajesTotalPortatiles = $this->generarMensajesTotalPortatiles();
        $mensajes = array_merge($mensajes, $mensajesTotalPortatiles);
        
        // Agregar mensajes con datos curiosos de programas
        $mensajesProgramas = $this->getDatosCuriososProgramas();
        $mensajes = array_merge($mensajes, $mensajesProgramas);
        
        // Agregar mensajes de todas las categorías para asegurar variedad
        $mensajesMotivacionales = $this->generarMensajesMotivacionales();
        $mensajesTecnologia = $this->generarMensajesTecnologia();
        $mensajesInstitucionales = $this->generarMensajesInstitucionales();
        $mensajesFormacion = $this->generarMensajesFormacion();
        $mensajesHabitos = $this->generarMensajesHabitos();
        $mensajesEmpleabilidad = $this->generarMensajesEmpleabilidad();
        $datosCuriosos = $this->generarDatosCuriosos();
        
        // Seleccionar un número limitado de cada categoría para no saturar
        $categoriasAdicionales = [
            $mensajesMotivacionales,
            $mensajesTecnologia, 
            $mensajesInstitucionales,
            $mensajesFormacion,
            $mensajesHabitos,
            $mensajesEmpleabilidad,
            $datosCuriosos
        ];
        
        // Agregar mensajes de cada categoría, seleccionando aleatoriamente
        foreach ($categoriasAdicionales as $categoria) {
            // Mezclar para tomar diferentes cada vez
            shuffle($categoria);
            // Tomar algunos mensajes de cada categoría (entre 2 y 5)
            $mensajesSeleccionados = array_slice($categoria, 0, rand(2, 5));
            $mensajes = array_merge($mensajes, $mensajesSeleccionados);
        }

        // Limpiar mensajes vacíos y duplicados
        $mensajesLimpios = array_values(array_unique(array_filter($mensajes)));
        
        // Mezclar los mensajes para mayor variedad en cada carga
        shuffle($mensajesLimpios);
        
        return $mensajesLimpios;
    }
} 