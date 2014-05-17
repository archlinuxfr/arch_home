<?php

/*****************************************************************************
* 2009 - 2014, Tuxce <tuxce.net@gmail.com>
* Page d'accueil de la communauté francophone pour la distribution Arch Linux
* http://archlinux.fr/home
*****************************************************************************
*/


$url="//archlinux.fr/home";
$search_site = array (
	array ("https://www.google.fr", "/search?q=", "Google"),
	array ("//forums.archlinux.fr", "/search.php?keywords=", "Forums"),
	array ("//wiki.archlinux.fr", "/index.php?title=Special:Search&go=Lire&search=", "Wiki"),
	array ("//archlinux.fr/irc/log.php", "?q=", "IRC"),
	array ("https://aur.archlinux.org", "/packages.php?O=0&do_Search=Aller&K=", "AUR"),
	array ("https://bugs.archlinux.org", "/?projet=0&string=", "Bugs")
	);

if (!empty ($_POST['q']) and !empty ($_POST['sub']))
{		
	$url="//archlinux.fr/home";
	if (isset ($search_site[$_POST['sub']-1]))
		$url = $search_site[$_POST['sub']-1][0] . 
			$search_site[$_POST['sub']-1][1] . $_POST['q'];
	header("Location: $url ");
	exit(0);
}

$feed_news = array ("Nouvelles", "//archlinux.fr", 
	"https://archlinux.fr/feed",
	3, "feed_1.xml");
$feed_updates = array (
	array ("[core][extra][community]", "//archlinux.fr", 
		"https://www.archlinux.org/feeds/packages/",
		5, "feed_2.xml"),
	array ("[archlinuxfr]", "//archlinux.fr", 
		"https://afur.archlinux.fr/feed.php",
		5, "feed_3.xml")
	);

if (!function_exists ("file_put_contents"))
{
	function file_put_contents ($file, $string)
	{
		$fd=fopen ($file, "w");
		if ($fd)
		{
			fwrite ($fd, $string);
			fclose ($fd);
			return true;
		}
		return false;
	}
}

function get_rss($feed,$objets) 
{
	$url = $feed[2];
	$file = $feed[4];
	if (isset ($_GET['nocache']))
		$cache = false;
	else
		$cache = true;
	if (isset ($_GET['delai']) and $_GET['delai'] + 0 != 0)
		$delai = $_GET['delai'];
	else
		$delai = 1440;
	if ($cache and is_file ($file))
	{
		$last_change = filemtime ($file);
		if ($last_change !== false and
			$last_change <	mktime (date ("H"),date ("i") - $delai))
		{
			$cache=false;
			
		}
	}
	else
		$cache=false;
	if ($cache)
	{
		$chaine = file_get_contents ($file);
		echo "<!-- CACHE -->\n";
	}
	else
	{
		$chaine = file_get_contents ($url);
		file_put_contents ($file, $chaine);
		echo "<!-- LIVE -->\n";
	}

			
	if(!empty ($chaine)) 
	{
		$tmp = preg_split("/<\/?"."item".">/",$chaine);
		for($i=1;$i<sizeof($tmp)-1;$i+=2)
			foreach($objets as $objet) 
			{
				$tmp2 = preg_split("/<\/?".$objet.">/",$tmp[$i]);
				$resultat[$i-1][] = @$tmp2[1];
			}
		return $resultat;
	}
}

function print_updates ($feed)
{
	$rss = get_rss($feed, array ('title', 'link'));
	if (empty ($rss))
	{
		echo "Aucune entree";
		return;
	}
	echo "<table id='updates_t'>";
	$i=0;
	foreach ( $rss as $item ) 
	{
		$i++;
		if ($i > $feed[3]) break;
		if (($arch_pos = strpos ($item[0], 'i686')) !== false)
		{
			$pkg=substr ($item[0], 0, $arch_pos);
			$arch = "i686";
		}
		else if (($arch_pos = strpos ($item[0], 'x86_64')) !== false)
		{
			$pkg=substr ($item[0], 0, $arch_pos);
			$arch = "x86_64";
		}
		else
		{
			$arch_pos = strpos ($item[0], ' any');
			$pkg=substr ($item[0], 0, $arch_pos + 1);
			$arch = "any";
		}
?>

<tr>
	<td><a href='<?php echo $item[1]; ?>' title='<?php echo $pkg; ?>'><?php echo $pkg; ?></a></td>
	<td style="text-align: right;"><?php echo $arch; ?></td>
</tr>

<?php
	}
	echo "</table>";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr"> 
	<head>
		<title>Archlinux.fr</title>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="content-language" content="fr-standard" />
		<link rel="icon" href="/commun/images/arch-francophonieb.png" type="image/png" />
		<link rel="stylesheet" media="all" type="text/css" href="home.css" />
	</head>
	<body onload="document.forms['f'].elements['q'].focus();document.forms['f'].elements['sub'][0].checked = true">
		
		<div id="top">
		<?php 
		echo $feed_news[0] . ":&nbsp;&nbsp;";
		$rss = get_rss($feed_news,array("title","link"));
		if (!empty ($rss))
		{
			$i=0;
			foreach ($rss as $item)
			{
				$i++;
				if ($i > $feed_news[3])
					break;
				echo "\t\t\t<a href='{$item[1]}'>{$item[0]}</a>&nbsp;&nbsp;\n";
			}
		}
		?>
		</div>
		<div id="cont">
		<a href="//archlinux.fr" title="Arch Linux"><img src="logo.png" alt="Logo" /></a><br />
		<form name="f" method="post">
<?php $i=1; foreach ($search_site as $site): ?>
			<input type="radio" name="sub" value="<?php echo $i++; ?>"/>
			<a href="<?php echo $site[0]; ?>"><?php echo $site[2] ?></a> &nbsp;&nbsp;
<?php endforeach; ?>
			<br /><br />
			<input id="q" name="q" type="text" size="40" /><input id="search_btn" type="submit" value="Chercher" />
		</form>
		</div>

		<div id="updates">
			<h2>Mise à jour</h2>
		<table><tr>
			<td><h3><?php echo $feed_updates[0][0] ?></h3></td>
			<td><h3><?php echo $feed_updates[1][0] ?></h3></td>
		</tr><tr><td>
			<?php print_updates ($feed_updates[0]); ?>
		</td>
		<td>
			<?php print_updates ($feed_updates[1]); ?>
		</td>
		</tr></table>
		</div>
		<div id="footer">
			<a href="http://forums.archlinux.fr/topic4316.html">Communauté Arch Linux</a>. Inspiré de <a href="http://github.com/pyther/arch-home/">arch_home (github)</a>
		</div>
	</body>
</html>

