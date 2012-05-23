<?php

    function do_search($dir, $file_mask, $search_mask, $search_type, $ri=0)
    {
        $ret = FALSE;
        $preg_file_mask = '|^'.trim(str_replace(Array('.','?','*'),Array('\.','.','.*'),$file_mask)).'$|';

        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file=='.' OR $file=='..') continue;
                    if (is_dir($dir.$file))
                    {
                        $next_dir = $dir.$file.'/';
                        echo '<div class="result res_dir" style="padding-left:'.(20*$ri).'px">
                                <input type="checkbox" id="'.md5($next_dir).'" onclick="change_color(this);">
                                <label for="'.md5($next_dir).'">'.$next_dir.'</label>
                              </div>';
                        $r = do_search($next_dir, $file_mask, $search_mask, $search_type, $ri+1);
                        if ($r AND !$ret) $ret = $r;
                    }
                    elseif (preg_match($preg_file_mask, $file))
                    {
                        $filename = $dir.$file;
                        $class='res_file_not_found';

                        $data = file_get_contents($filename);
                        if ($data===FALSE) {
                            $class='res_file_read_error';
                        } else
                        {
                            switch ($search_type)
                            {
                                case 'strpos': if (strpos($data,$search_mask)!==FALSE) $class='res_file_found'; $ret = TRUE; break;
                                case 'preg_match': if (preg_match($search_mask, $data)) $class='res_file_found'; $ret = TRUE; break;
                            }
                        }

                        echo '<div class="result '.$class.'" style="padding-left:'.(10*$ri).'px">
                                <input type="checkbox" id="'.md5($filename).'" onclick="change_color(this);">
                                <label for="'.md5($filename).'">'.$filename.'</label>
                        </div>';
                    }
                }
                closedir($dh);
            }
        }
        return $ret;
    }

    $search_types = Array('strpos', 'preg_match');
    $search_filetypes = Array('*.php', '*.txt', '*.*', '*');

    $mask = 'text';
    if (!empty($_REQUEST['mask']))
    {
        $mask = $_REQUEST['mask'];
    }

    $search_type = $search_types[0];
    if (    !empty($_REQUEST['search_type']) AND
            in_array($_REQUEST['search_type'], $search_types)
    ){
        $search_type = $_REQUEST['search_type'];
    }

    $dir = dirname(__FILE__).'/';
    if (    !empty($_REQUEST['dir']) AND
            file_exists($_REQUEST['dir']) AND
            is_dir($_REQUEST['dir'])
    ){
        $dir = preg_replace('|^.+[^\\\\/]$|','$0/',$_REQUEST['dir']);
    }

    $search_filetype = $search_filetypes[0];
    if (    !empty($_REQUEST['search_filetype']) AND
            in_array($_REQUEST['search_filetype'], $search_filetypes)
    ){
        $search_filetype = $_REQUEST['search_filetype'];
    }

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>File search</title>
    <style type="text/css">
    /*<![CDATA[*/
        body {font-family:Verdana;}
        #topheader { background-color: #0066CC; color:white; border-bottom:2px solid #0054A8; padding:20px;}
        .clear {clear:both;}
        .main .field {clear:both; text-align:right; line-height:25px;}
        .main label {float:left; padding-right:10px;}
        .main {float:left}
        .main input[type=text] { width:400px;}
        .main select { width:406px;}
        hr {height:1px; border:none; color:silver; background-color:silver;}
        #results {font-size:11px;}
        .result {padding:4px 0px;}
        .res_dir {color:blue; font-weight:bold;}
        .res_file_not_found {color:silver;}
        .res_file_found {color:green; background-color:yellow; padding:4px;}
        .res_file_read_error {color:red;}
        #results_header {margin:10px 0px; font-size:12px; border-bottom:2px solid #0066CC; padding-bottom:10px;}
        #examples {margin-left:20px;font-size:11px;float:left;}
        #examples ul {margin-top:0px;}
        #examples ul li {margin-bottom:10px;}
        #examples div, #examples span, #examples a {color:yellow;}
        .check-box-checked {background-color:#000000; color:#00FF33;}

    /*]]>*/
    </style>
    <script language="JavaScript" src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
    <script language="JavaScript" type="text/javascript">
    /*<![CDATA[*/

        function change_color(checkbox)
        {
            if ($(checkbox).is(':checked'))
            {
                $(checkbox).parent().addClass('check-box-checked');
            }
            else
            {
                $(checkbox).parent().removeClass('check-box-checked');
            }
        }

        function show(type)
        {
            if (type=='all') {
                $('.result').show('fast');
            }
            else
            {
                if (type=='with-mask') {
                    $('.res_file_found').show('fast');
                    $('.res_file_not_found').hide('fast');
                    $('.res_dir').hide('fast');
                    $('.res_file_read_error').hide('fast');
                }
                else if (type=='with-mask-dir') {
                    $('.res_dir').show('fast');
                    $('.res_file_found').show('fast');
                    $('.res_file_not_found').hide('fast');
                    $('.res_file_read_error').hide('fast');
                }
                else if (type=='without-mask') {
                    $('.res_file_not_found').show('fast');
                    $('.res_file_found').hide('fast');
                    $('.res_dir').hide('fast');
                    $('.res_file_read_error').hide('fast');
                }
                else if (type=='without-mask-dir') {
                    $('.res_file_not_found').show('fast');
                    $('.res_dir').show('fast');
                    $('.res_file_found').hide('fast');
                    $('.res_file_read_error').hide('fast');
                }
                else if (type=='errors') {
                    $('.res_file_read_errore').show('fast');
                    $('.res_file_not_found').hide('fast');
                    $('.res_file_found').hide('fast');
                    $('.res_dir').hide('fast');
                }
                else if (type=='errors-dir') {
                    $('.res_file_read_error').show('fast');
                    $('.res_dir').show('fast');
                    $('.res_file_not_found').hide('fast');
                    $('.res_file_found').hide('fast');
                }
            }
        }
    /*]]>*/
    </script>
</head>

<body>
<div id="topheader">
      <div class="main">
        <form action="?" method="POST">
         <div class="field">
            <label for="mask">Mask</label>
            <input type="text" name="mask" id="mask" value="<?php echo htmlspecialchars($mask);?>" />
         </div>

         <div class="field">
            <label for="search_type">Search type</label>
            <select name="search_type" id="search_type">
              <?php foreach ($search_types as $stype):?>
              <option value="<?php echo $stype;?>" <?php echo ($search_type==$stype)?'selected="selected"':'';?>><?php echo $stype;?></option>
              <?php endforeach;?>
            </select>
         </div>

         <div class="field">
            <label for="dir">Dir</label>
            <input type="text" id="dir" name="dir" value="<?php echo htmlspecialchars($dir);?>" />
         </div>

         <div class="field">
            <label for="search_filetype">File types</label>
            <select name="search_filetype" id="search_filetype">
              <?php foreach ($search_filetypes as $sftype):?>
              <option value="<?php echo $sftype;?>" <?php echo ($search_filetype==$sftype)?'selected="selected"':'';?>><?php echo $sftype;?></option>
              <?php endforeach;?>
            </select>
         </div>

         <div class="field">
            <hr />
            <label for="submit">&nbsp;</label>
            <input type="submit" id="submit" name="submit" value="Search for files" />
         </div>
        </form>

      </div>

    <div id="examples">
        <ul>
            <li>Example mask for `strpos` search type: <div>public function __construct</div></li>
            <li>Example mask for `preg_match` search type: <div>~public\sfunction\s__construct\(\$var_[a-z]+\)~Usix</div></li>
            <li>Latest version of script in: <a href="https://github.com/orlov0562/PHP-File-Searcher">GitHub</a></li>
        </ul>
    </div>
    <div class="clear"></div>
</div>

<?php if (!empty($_REQUEST['submit'])):?>
    <div id="results_header">
        <strong>Results:</strong>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Show only:
        <a href="#" onclick="show('all'); return false;">All</a> -
        <a href="#" onclick="show('with-mask'); return false;" style="background-color:yellow;">&nbsp;With mask&nbsp;</a> -
        <a href="#" onclick="show('with-mask-dir'); return false;" style="background-color:yellow;">&nbsp;With mask and dir&nbsp;</a> -
        <a href="#" onclick="show('without-mask'); return false;" style="color:gray;">Without mask</a> -
        <a href="#" onclick="show('without-mask-dir'); return false;" style="color:gray;">Without mask and dir</a> -
        <a href="#" onclick="show('errors'); return false;" style="color:red;">Errors</a> -
        <a href="#" onclick="show('errors-dir'); return false;" style="color:red;">Errors and dir</a>
    </div>

    <div id="results">
    <?php
        $res = do_search($dir, $search_filetype, $mask, $search_type);
        if (!$res) echo '<hr />Files with selected criterias not found!';
    ?>
    </div>

<?php endif;?>
</body>

</html>
