<?php
if(!isset($_PJ_include_path)) {
	print "\$_PJ_include_path ist nicht festgelegt (" . __FILE__ . ", Zeile: " . __LINE__ . ")";
	exit;
}

include_once("$_PJ_include_path/fpdf.inc.php");

class PJPDF extends FPDF {
	var $page = 0;

	function PDF($orientation = 'P', $unit = 'mm') {
		$this->FPDF($orientation, $unit);
	}

	function Header() {
		$small_font_size	= 10;
		$header_font_size	= 12;
		$left_margin		= $GLOBALS['_PJ_pdf_left_margin'];

		$line_y = 41;
		$img_height = 29/2+1;
		$img_width = 100/2;

		// if logo for PDF accounting report exists
		if($GLOBALS['_PJ_pdf_logo'] && file_exists($GLOBALS['_PJ_root'] . '/images/' . $GLOBALS['_PJ_pdf_logo'])) {
			$this->Image($GLOBALS['_PJ_root'] . '/images/' . $GLOBALS['_PJ_pdf_logo'],
						 $this->w/2 - $GLOBALS['_PJ_pdf_logo_width']/2,
						 $GLOBALS['_PJ_pdf_logo_top'],
						 $GLOBALS['_PJ_pdf_logo_width'],
						 $GLOBALS['_PJ_pdf_logo_height']);
			return;
		}

		// add default page header
		$this->SetLineWidth(0.1);
		$this->Line(0, $line_y, $this->w,$line_y);
		if($GLOBALS['_PJ_pdf_print_margins']) {
			$this->SetDrawColor(200);
			$this->Line($GLOBALS['_PJ_pdf_left_margin'], 0, $GLOBALS['_PJ_pdf_left_margin'], $this->h);
			$this->Line($this->w - $GLOBALS['_PJ_pdf_right_margin'], 0, $this->w - $GLOBALS['_PJ_pdf_right_margin'], $this->h);
			$this->Line(0, $GLOBALS['_PJ_pdf_top_margin'], $this->w,$GLOBALS['_PJ_pdf_top_margin']);
			$this->Line(0, $this->h - $GLOBALS['_PJ_pdf_bottom_margin'], $this->w,$this->h - $GLOBALS['_PJ_pdf_bottom_margin']);
		}

		$this->SetFont($GLOBALS['_PJ_pdf_font_face'],'',$header_font_size);
		$this->SetX($left_margin-3);
		$name = $GLOBALS['_PJ_pdf_header_string'];
		$this->Cell($this->GetStringWidth($name), $header_font_size+2, $name, 0, 1, 'L', 0);
		$this->SetX($left_margin-3);
		$this->SetFont($GLOBALS['_PJ_pdf_font_face'],'',$small_font_size-1);
		$name = $GLOBALS['_PJ_pdf_subheader_string'];
		$this->Cell($this->GetStringWidth($name), $header_font_size+2, $name, 0, 1, 'L', 0);
	}

	function AcceptPageBreak () {
		$this->page++;
        return false;
	}

	function Footer() {
		$this->SetFillColor($GLOBALS['_PJ_pdf_footer_bg_r'], $GLOBALS['_PJ_pdf_footer_bg_g'], $GLOBALS['_PJ_pdf_footer_bg_b']);
		$this->SetTextColor($GLOBALS['_PJ_pdf_footer_fg_r'], $GLOBALS['_PJ_pdf_footer_fg_g'], $GLOBALS['_PJ_pdf_footer_fg_b']);
	    $this->SetY(-$GLOBALS['_PJ_pdf_footer_margin']);
	    if($this->PageNo() > 1) {
	    	$this->SetFont($GLOBALS['_PJ_pdf_font_face'],'',10);
	    	$this->Cell(0, 10, $GLOBALS['_PJ_strings']['page'] . ' ' . $this->PageNo() . ' ' . $GLOBALS['_PJ_strings']['of'] . ' {nb}', 0, 0, 'C');
	    } else {
			$this->SetY(-$GLOBALS['_PJ_pdf_footer_margin']);
			$this->SetX($GLOBALS['_PJ_pdf_left_margin']);
			$this->SetFont($GLOBALS['_PJ_pdf_font_face'],'',6);
			$this->MultiCell($this->w - $GLOBALS['_PJ_pdf_left_margin'] - $GLOBALS['_PJ_pdf_right_margin'], 11, $GLOBALS['_PJ_pdf_footer_string'], 0, "C", 0);
		}
	}

	function calculateLeft(&$field_widths) {
		$spare_space = $this->w - $GLOBALS['_PJ_pdf_left_margin'] - $GLOBALS['_PJ_pdf_right_margin'] - $GLOBALS['_PJ_pdf_table_cell_spacing'];
		$open_count = 0;
		while(list($name, $width) = each($field_widths)) {
			if($width < 0) {
				++$open_count;
				$spare_space -= $GLOBALS['_PJ_pdf_table_cell_spacing'];
				continue;
			}
			$spare_space -= ($field_widths[$name] + $GLOBALS['_PJ_pdf_table_cell_spacing']);
		}

		if($open_count > 0)
			$spare_space = round($spare_space/$open_count);

		$left = $GLOBALS['_PJ_pdf_left_margin'] + $GLOBALS['_PJ_pdf_table_cell_spacing'];
		reset($field_widths);
		while(list($name, $width) = each($field_widths)) {
			if($width == 0) {
				$field_lefts[$name] = -1;
				continue;
			}
			if($width < 0)
				$field_widths[$name] = $spare_space;

			if($name_buffer != '')
				$left = $field_lefts[$name_buffer] + $field_widths[$name_buffer] + $GLOBALS['_PJ_pdf_table_cell_spacing'];

			$field_lefts[$name] = $left;
			$name_buffer = $name;
		}
		return $field_lefts;
	}

	function nbLines($w, $txt) {
		//Computes the number of lines a MultiCell of width w will take
		$cw	=& $this->CurrentFont['cw'];
		if($w==0)
			$w=$this->w-$this->rMargin-$this->x;
		
		$wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;

		$s = str_replace("\r", '', $txt);
		$nb = strlen($s);
		if($nb > 0 && $s[$nb-1] == "\n")
			$nb--;

		$sep	= -1;
		$i		= 0;
		$j		= 0;
		$l		= 0;
		$nl		= 1;
		while($i < $nb) {
			$c = $s[$i];
			if($c == "\n") {
				$i++;
				$sep	= -1;
				$j		= $i;
				$l		= 0;
				$nl++;
				continue;
			}
			if($c == ' ')
				$sep=$i;

			$l += $cw[$c];
			if($l > $wmax) {
				if($sep == -1) {
					if($i == $j)
						$i++;
				} else
					$i = $sep+1;

				$sep	= -1;
				$j		= $i;
				$l		= 0;
				$nl++;
			} else
				$i++;
		}
		return $nl;
	}
}

?>