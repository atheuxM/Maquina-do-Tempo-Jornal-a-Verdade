<?php
/**
 * Feed RSS Dinâmico - Publicações do Dia em Anos Anteriores
 * 
 * Este script gera um feed RSS com todas as matérias publicadas
 * no dia atual (mês-dia) em anos anteriores
 */

// Configurações
header('Content-Type: application/rss+xml; charset=utf-8');

// Caminho para o arquivo CSV
$csvFile = 'materias_url_averdade.csv';

// Configurações do site
$siteTitle = 'A Verdade - Maquina do Tempo';
$siteUrl = 'https://averdade.org.br';
$siteDescription = 'Reviva as matérias publicadas neste dia em anos anteriores';

// Pega a data atual no formato MM-DD
$dataHoje = date('m-d');

// Função para ler e filtrar o CSV
function obterMateriasDodia($arquivo, $dataFiltro) {
    $materias = [];
    
    if (!file_exists($arquivo)) {
        return $materias;
    }
    
    $handle = fopen($arquivo, 'r');
    
    // Pula o cabeçalho
    $header = fgetcsv($handle);
    
    // Lê cada linha
    while (($row = fgetcsv($handle)) !== false) {
        // Verifica se tem dados suficientes
        if (count($row) >= 4) {
            $titulo = trim($row[0]);
            $data = trim($row[1]);
            $ano = trim($row[2]);
            $url = trim($row[3]);
            
            // Filtra pela data (MM-DD)
            if ($data === $dataFiltro && !empty($titulo) && !empty($url)) {
                $materias[] = [
                    'titulo' => $titulo,
                    'data' => $data,
                    'ano' => $ano,
                    'url' => $url
                ];
            }
        }
    }
    
    fclose($handle);
    
    // Ordena por ano (mais recente primeiro)
    usort($materias, function($a, $b) {
        return (int)$b['ano'] - (int)$a['ano'];
    });
    
    return $materias;
}

// Função para escapar texto XML
function escaparXml($texto) {
    return htmlspecialchars($texto, ENT_XML1, 'UTF-8');
}

// Função para formatar data completa
function formatarDataCompleta($data, $ano) {
    list($mes, $dia) = explode('-', $data);
    $dataObj = DateTime::createFromFormat('Y-m-d', "$ano-$mes-$dia");
    
    if ($dataObj) {
        // Retorna no formato RFC 822 para RSS
        return $dataObj->format('D, d M Y H:i:s O');
    }
    
    return date('D, d M Y H:i:s O');
}

// Busca matérias do dia
$materias = obterMateriasDodia($csvFile, $dataHoje);

// Gera o XML do feed RSS
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title><?php echo escaparXml($siteTitle); ?></title>
    <link><?php echo escaparXml($siteUrl); ?></link>
    <description><?php echo escaparXml($siteDescription); ?></description>
    <language>pt-BR</language>
    <lastBuildDate><?php echo date('D, d M Y H:i:s O'); ?></lastBuildDate>
    <atom:link href="<?php echo escaparXml($siteUrl . $_SERVER['PHP_SELF']); ?>" rel="self" type="application/rss+xml" />
    
    <?php if (empty($materias)): ?>
    <item>
        <title>Nenhuma matéria publicada neste dia em anos anteriores</title>
        <description>Não há registros de publicações para o dia <?php echo date('d/m'); ?> em anos anteriores.</description>
        <pubDate><?php echo date('D, d M Y H:i:s O'); ?></pubDate>
    </item>
    <?php else: ?>
        <?php foreach ($materias as $materia): ?>
    <item>
        <title><?php echo escaparXml('[' . $materia['ano'] . '] ' . $materia['titulo']); ?></title>
        <link><?php echo escaparXml($materia['url']); ?></link>
        <guid><?php echo escaparXml($materia['url']); ?></guid>
        <description><?php echo escaparXml('Matéria publicada originalmente em ' . $materia['ano'] . ': ' . $materia['titulo']); ?></description>
        <pubDate><?php echo formatarDataCompleta($materia['data'], $materia['ano']); ?></pubDate>
    </item>
        <?php endforeach; ?>
    <?php endif; ?>
</channel>
</rss>
