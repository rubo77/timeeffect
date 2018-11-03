<?php
if(!isset($_PJ_include_path)) {
	print "\$_PJ_include_path ist nicht festgelegt (" . __FILE__ . ", Zeile: " . __LINE__ . ")";
	exit;
}

include_once("$_PJ_include_path/fpdf.inc.php");
include_once("$_PJ_include_path/pdflayout.inc.php");

class PJPDF extends FPDF {
	var $page = 0;

	function PDF($orientation = 'P', $unit = 'mm') {
		$this->FPDF($orientation, $unit);
	}

	function Header() {
		if(!$GLOBALS['_PJ_pdf_print_header']) {
			return;
		}
		$small_font_size	= $GLOBALS['_PJ_pdf_small_font_size'];
		$header_font_size	= $GLOBALS['_PJ_pdf_header_font_size'];
		$left_margin		= $GLOBALS['_PJ_pdf_left_margin'];

		$line_y = $this->GetY() + $header_font_size + 2;

		// if logo for PDF accounting report exists
		if($GLOBALS['_PJ_pdf_logo'] && file_exists($GLOBALS['_PJ_root'] . '/images/' . $GLOBALS['_PJ_pdf_logo'])) {
			switch($GLOBALS['_PJ_pdf_logo_align']) {
				case 'L':
					$logo_left	= $left_margin;
					break;
				case 'R':
					$logo_left	= $this->w - $GLOBALS['_PJ_pdf_right_margin'] - $GLOBALS['_PJ_pdf_logo_width'];
					break;
				case 'C':
				default:
					$logo_left	= $this->w/2 - $GLOBALS['_PJ_pdf_logo_width']/2;
					break;
			}
			$this->Image($GLOBALS['_PJ_root'] . '/images/' . $GLOBALS['_PJ_pdf_logo'],
						 $logo_left,
						 $GLOBALS['_PJ_pdf_logo_top'],
						 $GLOBALS['_PJ_pdf_logo_width'],
						 $GLOBALS['_PJ_pdf_logo_height']);
		}

		// add default page header
		if($GLOBALS['_PJ_pdf_print_header_line']) {
			$this->SetLineWidth(0.1);
			$this->Line(0, $line_y, $this->w,$line_y);
		}
		if($GLOBALS['_PJ_pdf_print_margins']) {
			$this->SetDrawColor(200);
			$this->Line($GLOBALS['_PJ_pdf_left_margin'], 0, $GLOBALS['_PJ_pdf_left_margin'], $this->h);
			$this->Line($this->w - $GLOBALS['_PJ_pdf_right_margin'], 0, $this->w - $GLOBALS['_PJ_pdf_right_margin'], $this->h);
			$this->Line(0, $GLOBALS['_PJ_pdf_top_margin'], $this->w,$GLOBALS['_PJ_pdf_top_margin']);
			$this->Line(0, $this->h - $GLOBALS['_PJ_pdf_bottom_margin'], $this->w,$this->h - $GLOBALS['_PJ_pdf_bottom_margin']);
		}

		if($GLOBALS['_PJ_pdf_print_header_string']) {
			$this->SetFont($GLOBALS['_PJ_pdf_font_face'],'',$header_font_size);
			$this->SetX($left_margin-3);
			$name = $GLOBALS['_PJ_pdf_header_string'];
			$this->Cell($this->GetStringWidth($name), $header_font_size+2, $name, 0, 1, 'L', 0);
			$this->SetX($left_margin-3);
			$this->SetFont($GLOBALS['_PJ_pdf_font_face'],'',$small_font_size-1);
			$name = $GLOBALS['_PJ_pdf_subheader_string'];
			$this->Cell($this->GetStringWidth($name), $header_font_size+2, $name, 0, 1, 'L', 0);
		}
	}

	function AcceptPageBreak () {
		$this->page++;
        return false;
	}

	function Footer() {
		if(!$GLOBALS['_PJ_pdf_print_footer']) {
			return;
		}
		$this->SetFillColor($GLOBALS['_PJ_pdf_footer_bg_r'], $GLOBALS['_PJ_pdf_footer_bg_g'], $GLOBALS['_PJ_pdf_footer_bg_b']);
		$this->SetTextColor($GLOBALS['_PJ_pdf_footer_fg_r'], $GLOBALS['_PJ_pdf_footer_fg_g'], $GLOBALS['_PJ_pdf_footer_fg_b']);
		$this->SetY($this->h - $GLOBALS['_PJ_pdf_footer_margin']);
	    if($this->PageNo() > 1) {
	    	$this->SetFont($GLOBALS['_PJ_pdf_font_face'],'',$GLOBALS['_PJ_pdf_footer_font_size']);
	    	$this->Cell(0, 10, $GLOBALS['_PJ_strings']['page'] . ' ' . $this->PageNo() . ' ' . $GLOBALS['_PJ_strings']['of'] . ' {nb}', 0, 0, 'C');
	    } else if($GLOBALS['_PJ_pdf_print_footer_string']) {
			$this->SetX($GLOBALS['_PJ_pdf_left_margin']);
			$this->SetFont($GLOBALS['_PJ_pdf_font_face'],'',$GLOBALS['_PJ_pdf_footer_font_size']);
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
