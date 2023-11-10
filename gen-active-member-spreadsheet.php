<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


// Add an action for the AJAX request
add_action('wp_ajax_ppa_generate_and_download_spreadsheet', 'ppa_generate_and_download_spreadsheet');
function ppa_generate_and_download_spreadsheet()
{
    // Generate active member spreadsheet and save to files.
    ppa_gen_active_member_spreadsheet();

    // Send the generated file for download
    $file_path = trailingslashit(WP_CONTENT_DIR) . 'ppa/active-member-data.xlsx';
    if (file_exists($file_path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    }
}

// Generate active member spreadsheet and save to files.
function ppa_gen_active_member_spreadsheet()
{
    $spreadsheet = new Spreadsheet();
    $active_worksheet = $spreadsheet->getActiveSheet();

    // Set column widths.
    $active_worksheet->getColumnDimension('A')->setWidth(40);
    $active_worksheet->getColumnDimension('B')->setWidth(40);
    $active_worksheet->getColumnDimension('C')->setWidth(40);

    // Style for user headers (green background)
    $userHeaderStyle = [
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['argb' => '58e47d'],
        ],
        'font' => [
            'bold' => true,
        ],
    ];

    // Set styles for event headers
    $eventHeaderStyle = [
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'ebebeb',
            ],
        ],
    ];

    $current_row = 1;
    $active_user_ids = ppa_get_active_user_ids_db();
    foreach ($active_user_ids as $user_id) {
        $approved_service_hour_requests = ppa_get_approved_service_hours_requests_db($user_id);

        // User headers
        $active_worksheet->setCellValue('A' . $current_row, "Member Name");
        $active_worksheet->setCellValue('B' . $current_row, "Email");
        $active_worksheet->setCellValue('C' . $current_row, "Total Service Hours");

        // Apply styles to user headers
        $active_worksheet->getStyle('A' . $current_row)->applyFromArray($userHeaderStyle);
        $active_worksheet->getStyle('B' . $current_row)->applyFromArray($userHeaderStyle);
        $active_worksheet->getStyle('C' . $current_row)->applyFromArray($userHeaderStyle);

        $current_row += 1;

        // User info
        $user_info = ppa_get_user_info_db($user_id);
        $active_worksheet->setCellValue('A' . $current_row, $user_info["full_name"]);
        $active_worksheet->setCellValue('B' . $current_row, $user_info["user_email"]);

        // Set the SUM formula for total hours
        $totalHoursCell = 'C' . $current_row;
        $sum_start = $current_row + 2;
        $sum_end = $sum_start + count($approved_service_hour_requests) - 1;
        $sum_str = $sum_start . ' - ' . $sum_end;
        $hoursFormula = '=SUM(C' . $sum_start . ':C' . $sum_end . ')';
        $active_worksheet->setCellValue($totalHoursCell, $hoursFormula);

        $current_row += 1;

        // Event headers
        $active_worksheet->setCellValue('A' . $current_row, "Event Name");
        $active_worksheet->setCellValue('B' . $current_row, "Date");
        $active_worksheet->setCellValue('C' . $current_row, "Service Hours");

        // Apply styles to event headers
        $active_worksheet->getStyle('A' . $current_row)->applyFromArray($eventHeaderStyle);
        $active_worksheet->getStyle('B' . $current_row)->applyFromArray($eventHeaderStyle);
        $active_worksheet->getStyle('C' . $current_row)->applyFromArray($eventHeaderStyle);

        $current_row += 1;

        // Events
        foreach ($approved_service_hour_requests as $request) {
            $active_worksheet->setCellValue('A' . $current_row, $request["title"]);
            $active_worksheet->setCellValue('B' . $current_row, $request["event_date"]);
            $active_worksheet->setCellValue('C' . $current_row, $request["hours"]);

            $current_row += 1;
        }
    }

    $savePath = trailingslashit(WP_CONTENT_DIR) . 'ppa/';
    $filename = 'active-member-data.xlsx';

    // Ensure that the directory exists
    if (!file_exists($savePath)) {
        mkdir($savePath, 0755, true);
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save($savePath . $filename);
}
?>