<?php

function renderContract($client, $car, $rental) {

    // 1. Sanitize & Format Data
    $clientName = htmlspecialchars($client['full_name'] ?? 'NOT PROVIDED');
    $license    = htmlspecialchars($client['license_number'] ?? 'NOT PROVIDED');
    $phone      = htmlspecialchars($client['phone'] ?? 'NOT PROVIDED');
    $brand      = htmlspecialchars($car['brand'] ?? 'NOT PROVIDED');
    $model      = htmlspecialchars($car['model'] ?? 'NOT PROVIDED');
    $plate      = htmlspecialchars($car['plate'] ?? 'NOT PROVIDED');
    
    $startRaw   = $rental['start_date'] ?? date('Y-m-d');
    $endRaw     = $rental['end_date'] ?? date('Y-m-d');
    $start      = date('d/m/Y', strtotime($startRaw));
    $end        = date('d/m/Y', strtotime($endRaw));
    $total      = number_format((float)($rental['total_price'] ?? 0), 2, ',', ' ');
    $today      = date('d/m/Y \a\t H:i');
    $contractId = "LOC-" . date('Y') . "-" . str_pad($rental['id'] ?? rand(100, 999), 5, '0', STR_PAD_LEFT);

    return "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Rental Contract - $contractId</title>
        <style>
            body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1f2937; line-height: 1.5; padding: 40px; font-size: 13px; }
            .header-table { width: 100%; border-bottom: 3px solid #111827; padding-bottom: 20px; margin-bottom: 30px; }
            .header-table td { vertical-align: top; }
            .company-info h1 { margin: 0; font-size: 24px; color: #111827; text-transform: uppercase; letter-spacing: 1px;}
            .company-info p { margin: 2px 0; color: #4b5563; font-size: 12px; }
            .contract-meta { text-align: right; }
            .contract-meta h2 { margin: 0; font-size: 20px; color: #dc2626; }
            .section { margin-bottom: 25px; }
            .section-title { font-weight: bold; font-size: 14px; background-color: #f3f4f6; padding: 8px 12px; border-left: 4px solid #111827; margin-bottom: 15px; text-transform: uppercase; }
            .data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
            .data-table th, .data-table td { padding: 10px; border: 1px solid #e5e7eb; text-align: left; }
            .data-table th { background-color: #f9fafb; width: 30%; color: #4b5563; font-weight: normal; }
            .data-table td { font-weight: bold; }
            .rtl { direction: rtl; text-align: right; }
            .legal-text { font-size: 10px; color: #4b5563; text-align: justify; column-count: 2; column-gap: 30px; }
            .legal-text h4 { margin-bottom: 5px; color: #111827; }
            .legal-text p { margin-top: 0; margin-bottom: 10px; }
            .signature-area { width: 100%; margin-top: 40px; table-layout: fixed; }
            .signature-area td { text-align: center; vertical-align: top; padding: 20px; }
            .sign-box { height: 120px; border: 2px dashed #d1d5db; margin-top: 15px; border-radius: 8px; position: relative; }
            .sign-box span { position: absolute; bottom: 10px; width: 100%; left: 0; color: #9ca3af; font-size: 11px; }
        </style>
    </head>
    <body>
        <table class='header-table'>
            <tr>
                <td class='company-info'>
                    <h1>AUTO AGENCY</h1>
                    <p>Boulevard de la Mecque, Laayoune</p>
                    <p>Phone: +212 6 00 00 00 00</p>
                    <p>Business Registration: 123456 | Trade License: 98765432</p>
                </td>
                <td class='contract-meta'>
                    <h2>CONTRACT NO. $contractId</h2>
                    <p>Issued on: <strong>$today</strong></p>
                    <p>Status: <strong style='color: #059669;'>Confirmed</strong></p>
                </td>
            </tr>
        </table>

        <div class='section'>
            <div class='section-title'>I. Tenant Information</div>
            <table class='data-table'>
                <tr>
                    <th>Full Name</th><td class='rtl'>$clientName</td>
                    <th>License Number</th><td class='rtl'>$license</td>
                </tr>
                <tr>
                    <th>Phone</th><td>$phone</td>
                    <th>Payment Method</th><td>Credit Card / Cash</td>
                </tr>
            </table>
        </div>

        <div class='section'>
            <div class='section-title'>II. Vehicle Description</div>
            <table class='data-table'>
                <tr>
                    <th>Make & Model</th><td>$brand $model</td>
                    <th>Registration Number</th><td class='rtl'>$plate</td>
                </tr>
                <tr>
                    <th>Fuel Level at Pickup</th><td>8/8 (Full Tank)</td>
                    <th>Mileage at Pickup</th><td>To be recorded</td>
                </tr>
            </table>
        </div>

        <div class='section'>
            <div class='section-title'>III. Rental Terms</div>
            <table class='data-table'>
                <tr>
                    <th>Start Date</th><td>$start</td>
                    <th>Expected Return Date</th><td>$end</td>
                </tr>
                <tr>
                    <th>Deposit / Security</th><td>5 000,00 MAD</td>
                    <th>Total Rental Amount</th><td style='font-size: 16px; color: #dc2626;'>$total MAD incl. tax</td>
                </tr>
            </table>
        </div>

        <div class='section'>
            <div class='section-title'>IV. General Terms and Conditions</div>
            <div class='legal-text'>
                <h4>Article 1: Use of the Vehicle</h4>
                <p>The tenant agrees to use the vehicle responsibly and exclusively within Morocco.</p>
                <h4>Article 2: Fuel</h4>
                <p>The vehicle is delivered with a specified fuel level. It must be returned with the same level.</p>
                <h4>Article 3: Infractions</h4>
                <p>The tenant remains solely responsible for fines, tickets, and notices.</p>
                <h4>Article 4: Insurance and Deposit</h4>
                <p>Vehicles are fully insured with a deductible applicable in case of accident.</p>
            </div>
        </div>

        <table class='signature-area'>
            <tr>
                <td><strong>The Lessor (The Agency)</strong><div class='sign-box'><span>Seal and Signature</span></div></td>
                <td><strong>The Lessee (The Client)</strong><br><small>To be preceded by the note 'Read and approved'</small><div class='sign-box'><span>Signature</span></div></td>
            </tr>
        </table>
    </body>
    </html>";
}