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

		$this->SetLineWidth(0.1);
		$this->Line(0, $line_y, $this->w,$line_y);
		if($GLOBALS['_PJ_pdf_print_margins']) {
			$this->SetDrawColor(200);
			$this->Line($GLOBALS['_PJ_pdf_left_margin'], 0, $GLOBALS['_PJ_pdf_left_margin'], $this->h);
			$this->Line($this->w - $GLOBALS['_PJ_pdf_right_margin'], 0, $this->w - $GLOBALS['_PJ_pdf_right_margin'], $this->h);
			$this->Line(0, $GLOBALS['_PJ_pdf_top_margin'], $this->w,$GLOBALS['_PJ_pdf_top_margin']);
			$this->Line(0, $this->h - $GLOBALS['_PJ_pdf_bottom_margin'], $this->w,$this->h - $GLOBALS['_PJ_pdf_bottom_margin']);
		}

		$this->SetFont('Arial','',$header_font_size);
		$this->SetX($left_margin-3);
		$name = $GLOBALS['_PJ_strings']['pdf_header'];
		$this->Cell($this->GetStringWidth($name), $header_font_size+2, $name, 0, 1, 'L', 0);
		$this->SetX($left_margin-3);
		$this->SetFont('Arial','',$small_font_size-1);
		$name = $GLOBALS['_PJ_strings']['pdf_subheader'];
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
	    $this->SetFont('Arial','',9);
	    $this->Cell(0, 10, $GLOBALS['_PJ_strings']['page'] . ' ' . $this->PageNo().' ' . $GLOBALS['_PJ_strings']['of'] . ' {nb}', 0, 0, 'C');
	    $this->SetY(-$GLOBALS['_PJ_pdf_footer_margin']+20);
	    $this->SetX($GLOBALS['_PJ_pdf_left_margin']);
	    $this->SetFont('Arial','',7.5);
	    $this->MultiCell($this->w - $GLOBALS['_PJ_pdf_left_margin'] - $GLOBALS['_PJ_pdf_right_margin'], 11, $GLOBALS['_PJ_pdf_footer_string'], 0, "C", 0);
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
}

?>