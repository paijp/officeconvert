<?php

$tmpdir = "./work/";
$extslist = "xls/xlsx/doc/docx/ppt/pptx/odt/ods/odp";


function	my_ini_get($key)
{
	return $key.":".ini_get($key);
}


if (($fn0 = @$_FILES["f0"]["tmp_name"]) === null) {
	$s = my_ini_get("post_max_size")." ".my_ini_get("upload_max_filesize");
	print <<<EOO
<HTML><HEAD><TITLE>officeconvert</TITLE></HEAD><BODY>
<H1>officeconvert</H1>

<FORM method=POST enctype="multipart/form-data">
<UL>
	<LI>{$s}
	<LI><INPUT type=file name=f0>
	<LI>convert-to: <SELECT name=t0>
<OPTION value=pdf selected>* -&gt; .pdf</OPTION>
<OPTION value=txt>doc -&gt; .txt</OPTION>
<OPTION value=csv>* -&gt; .csv</OPTION>
<OPTION value=tsv>* -&gt; .tsv</OPTION>
	</SELECT>
	<LI><INPUT type=submit>
</UL>
</FORM>

<HR>
</BODY></HTML>

EOO;
	die();
}

$s = @$_FILES["f0"]["name"];
$exts = null;
foreach (explode("/", $extslist) as $val)
	if (eregi('\.'.$val.'$', $s)) {
		$exts = $val;
		break;
	}
if ($exts === null)
	die("extension not supported.");

$id = implode("_", split('[^0-9]+', @$_SERVER["REMOTE_ADDR"]." ".@$_SERVER["REMOTE_PORT"]));
$fns = "{$tmpdir}/{$id}.{$exts}";

$infilter = null;
$extdsub = "";
$ctype = "";
switch ($extd = @$_POST["t0"]) {
	default:
		die("convert-to not supported.");
	case	"pdf":
		$ctype = "application/pdf";
		break;
	case	"txt":
		$extdsub = ":Text";
		break;
	case	"csv":
		$infilter = "--infilter=CSV:44,34,UTF8";
		break;
	case	"tsv":
		$infilter = "--infilter=CSV:9,34,UTF8";
		break;
}
$fnd = "{$tmpdir}/{$id}.{$extd}";

if (!move_uploaded_file($fn0, $fns))
	die("move_uploaded_file failed.");

$s = `env HOME=/tmp LANG=en_US.UTF-8 libreoffice --headless --convert-to {$extd}{$extdsub} --outdir {$tmpdir} {$infilter} {$fns}`;
@unlink($fns);
if (!is_readable($fnd)) {
#	die("soffice error.");
	die("soffice error.<BR>".nl2br(htmlspecialchars($s)));
	
}

if ($ctype == "")
	header("Content-Type: text/plain; encode=UTF-8");
else
	header("Content-Type: {$ctype}");
header('Content-Disposition: inline; filename="convert.'.$extd.'"');
readfile($fnd);
@unlink($fnd);

?>
