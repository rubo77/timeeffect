<?php
	$_PJ_icon_width						= 32;
	$_PJ_icon_height					= 32;
	$_PJ_indent_width					= 20;
	$_PJ_top_frame_width				= 985;

	$_PJ_frame_cellpadding				= 0;
	$_PJ_frame_cellspacing				= 0;
	$_PJ_frame_border					= 0;

	$_PJ_inner_frame_cellpadding		= 3;
	$_PJ_inner_frame_cellspacing		= 1;
	$_PJ_inner_frame_border				= 0;

	$_PJ_left_frame_width				= 200;
	$_PJ_right_frame_width				= 0;
	$_PJ_content_frame_width			= $_PJ_top_frame_width - $_PJ_left_frame_width -
										  $_PJ_right_frame_width;

	$_PJ_top_head_back_width			= 80;
	$_PJ_top_head_forward_width			= 0;
	$_PJ_top_head_nav_width				= $_PJ_top_frame_width - $_PJ_top_head_back_width -
										  $_PJ_top_head_forward_width;

	$_PJ_customer_action_width			= 0;
	$_PJ_customer_budget_width			= 100;
	$_PJ_customer_name_width			= 250;
	$_PJ_customer_desc_width			= $_PJ_content_frame_width - $_PJ_customer_name_width -
										  $_PJ_customer_action_width - $_PJ_customer_budget_width;

	$_PJ_form_field_name_width			= 250;
	$_PJ_form_field_width				= $_PJ_content_frame_width - $_PJ_form_field_name_width;

	$_PJ_project_action_width			= 0;
	$_PJ_project_name_width				= 200;
	$_PJ_project_time_width				= 190;
	$_PJ_project_time_name_width		= 110;
	$_PJ_project_time_value_width		= $_PJ_project_time_width - $_PJ_project_time_name_width;
	$_PJ_project_status_width			= 0;
	$_PJ_project_desc_width				= $_PJ_content_frame_width - $_PJ_project_time_width -
										  $_PJ_project_name_width - $_PJ_project_status_width -
										  $_PJ_project_action_width;

	$_PJ_project_costs_width			= $_PJ_content_frame_width;
	$_PJ_project_costs_name_width		= $_PJ_project_costs_width - $_PJ_project_costs_value_width;
	$_PJ_project_costs_value_width		= 120;

	$_PJ_effort_date_width				= 120;
	$_PJ_effort_times_width				= 0;
	$_PJ_effort_action_width			= 0;
	$_PJ_effort_time_width				= 160;
	$_PJ_effort_time_value_width		= 75;
	$_PJ_effort_time_name_width			= $_PJ_effort_time_width - $_PJ_effort_time_value_width;
	$_PJ_effort_desc_width				= $_PJ_content_frame_width - $_PJ_effort_date_width -
										  $_PJ_effort_times_width - $_PJ_effort_time_width -
										  $_PJ_effort_action_width;

	$_PJ_stat_head_width				= 250;
	$_PJ_stat_content_width				= 100;
	$_PJ_stat_width						= $_PJ_stat_head_width + $_PJ_stat_content_width;

	$_PJ_statistics_days_width			= 100;
	$_PJ_statistics_weeks_width			= $_PJ_statistics_days_width;
	$_PJ_statistics_months_width		= $_PJ_statistics_days_width;
	$_PJ_statistics_name_width			= $_PJ_content_frame_width - $_PJ_statistics_days_width -
										  $_PJ_statistics_weeks_width - $_PJ_statistics_months_width;

	$_PJ_stat_months_day_width			= 120;
	$_PJ_stat_months_hours_width		= 100;
	$_PJ_stat_months_costs_width		= 75;
	$_PJ_stat_months_projects_width		= 150;
	$_PJ_stat_months_desc_width			= $_PJ_content_frame_width - $_PJ_stat_months_day_width -
										  $_PJ_stat_months_hours_width - $_PJ_stat_months_projects_width -
										  $_PJ_stat_months_costs_width;

	$_PJ_stat_time_day_width			= 120;
	$_PJ_stat_time_hours_width			= 60;
	$_PJ_stat_time_costs_width			= 75;
	$_PJ_stat_time_projects_width		= 150;
	$_PJ_stat_time_desc_width			= $_PJ_content_frame_width - $_PJ_stat_time_day_width -
										  $_PJ_stat_time_hours_width - $_PJ_stat_time_projects_width -
										  $_PJ_stat_time_costs_width;

	$_PJ_stat_projects_day_width		= 120;
	$_PJ_stat_projects_hours_width		= $_PJ_stat_projects_day_width;
	$_PJ_stat_projects_minutes_width	= $_PJ_stat_projects_day_width;
	$_PJ_stat_projects_billed_width		= 75;
	$_PJ_stat_projects_desc_width		= $_PJ_content_frame_width - $_PJ_stat_projects_day_width -
										  $_PJ_stat_projects_hours_width - $_PJ_stat_projects_minutes_width - $_PJ_stat_projects_billed_width;

	$_PJ_pdf_logo						= 'timeeffect.jpg';
	$_PJ_pdf_logo_width					= 140;
	$_PJ_pdf_logo_height				= 17.5;
	$_PJ_pdf_logo_top					= 46;

	$_PJ_pdf_top_margin					= 80;
	$_PJ_pdf_left_margin				= 50;
	$_PJ_pdf_head_right					= 60;
	$_PJ_pdf_bottom_margin				= 120;
	$_PJ_pdf_footer_margin				= 65;
	$_PJ_pdf_right_margin				= $_PJ_pdf_left_margin;
	$_PJ_pdf_table_cell_spacing			= 1;
	$_PJ_pdf_mini_font_size				= 6;
	$_PJ_pdf_small_font_size			= 9.5;
	$_PJ_pdf_header_font_size			= 12;
	$_PJ_pdf_print_margins				= false;
	$_PJ_pdf_sum_spacing				= 5;

	$_PJ_pdf_table_head_bg_r			= 120;
	$_PJ_pdf_table_head_bg_g			= 120;
	$_PJ_pdf_table_head_bg_b			= 120;
	$_PJ_pdf_table_head_fg_r			= 255;
	$_PJ_pdf_table_head_fg_g			= 255;
	$_PJ_pdf_table_head_fg_b			= 255;
	$_PJ_pdf_table_row0_bg_r			= 200;
	$_PJ_pdf_table_row0_bg_g			= 200;
	$_PJ_pdf_table_row0_bg_b			= 200;
	$_PJ_pdf_table_row1_bg_r			= 230;
	$_PJ_pdf_table_row1_bg_g			= 230;
	$_PJ_pdf_table_row1_bg_b			= 230;
	$_PJ_pdf_table_row0_fg_r			= 0;
	$_PJ_pdf_table_row0_fg_g			= 0;
	$_PJ_pdf_table_row0_fg_b			= 0;
	$_PJ_pdf_table_row1_fg_r			= 0;
	$_PJ_pdf_table_row1_fg_g			= 0;
	$_PJ_pdf_table_row1_fg_b			= 0;
	$_PJ_pdf_table_sum_bg_r				= 120;
	$_PJ_pdf_table_sum_bg_g				= 120;
	$_PJ_pdf_table_sum_bg_b				= 120;
	$_PJ_pdf_table_sum_fg_r				= 255;
	$_PJ_pdf_table_sum_fg_g				= 255;
	$_PJ_pdf_table_sum_fg_b				= 255;

	$_PJ_pdf_footnote_bg_r				= 255;
	$_PJ_pdf_footnote_bg_g				= 255;
	$_PJ_pdf_footnote_bg_b				= 255;
	$_PJ_pdf_footnote_fg_r				= 0;
	$_PJ_pdf_footnote_fg_g				= 0;
	$_PJ_pdf_footnote_fg_b				= 0;

	$_PJ_pdf_footer_bg_r				= 255;
	$_PJ_pdf_footer_bg_g				= 255;
	$_PJ_pdf_footer_bg_b				= 255;
	$_PJ_pdf_footer_fg_r				= 0;
	$_PJ_pdf_footer_fg_g				= 0;
	$_PJ_pdf_footer_fg_b				= 0;

	$_PJ_pdf_footer_string				= "TIMEEFFECT";

	$_PJ_calender_colours				= array(
											"#FF0000",
											"#00FF00",
											"#0000FF",
											"#FF00FF",
											"#FF6600",
											"#FF0066",
											"#0066FF",
											"#00FF66",
											"#6600FF",
											"#66FF00",
											"#FFCC00",
											"#FF00CC",
											"#00CCFF",
											"#00FFCC",
											"#CC00FF",
											"#CCFF00",
											"#FF5555",
											"#5555FF",
											"#55FF55",
											"#CC5555",
											"#5555CC",
											"#55CC55",
											"#FF9999",
											"#9999FF",
											"#99FF99",
											"#CC9999",
											"#9999CC",
											"#99CC99",
											"#FFFF00",
											"#00FFFF"
											);
?>