<?php
require_once "vendor/autoload.php";
use Sunra\PhpSimple\HtmlDomParser;

header('charset=windows-1251');
$url = 'http://forums.warforge.ru/';
$dom = HtmlDomParser::file_get_html($url);
$searchGame = $_POST['game'];
$searchKeyword = $_POST['keyword'];

$section = $url; $sectionvalue=0;
foreach($dom->find('.ipbtable') as $t)
    foreach($t->find('.row2 > b') as $b)
        if(!($b->parent->tag == 'span' or $b->parent->parent->parent->parent->id == 'fo_stat'))
        {
            //looking for best similarity
            $similar_value = similar_text($b->plaintext, $searchGame);
            //echo $similar_value . ' ' . $b->plaintext . PHP_EOL;
            if($similar_value > $sectionvalue)
            {
                $sectionvalue = $similar_value;
                $section = htmlspecialchars_decode($b->children(0)->href);
            }
        }
if($section == $url) {
    echo 'No game found';
    exit;
}
$searchResult = array();
$dom = HtmlDomParser::file_get_html($section);
foreach($dom->find('td[valign=middle]') as $topic) {
    $topic_title = $topic->find('a[id^=tid-link]')[0];
    $topic_title_similarity = similar_text($topic_title->plaintext, $searchKeyword);
    $topic_desc = $topic->find('span[id^=tid-desc]')[0];
    $topic_desc_similarity = similar_text($topic_desc->plaintext, $searchKeyword);
    //echo $topic_title->plaintext . ' = ' . $topic_desc->plaintext . ' ' . max($topic_title_similarity, $topic_desc_similarity) . PHP_EOL;
    if(max($topic_desc_similarity, $topic_title_similarity) > 0)
        $searchResult[] = $topic_title;
}

//list all results
foreach($searchResult as $s)
    echo "<a href='" . $s->href . "'>" . $s->plaintext . "</a>";
