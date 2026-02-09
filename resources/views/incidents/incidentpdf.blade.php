<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Information Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 15px; line-height: 1.4; }

        /* WATERMARK */
        .watermark-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1000;
        }

        .watermark {
           position: absolute;
            top: 40%;
            left: 40%;
            width: 800px;
            margin-left: -300px;
            text-align: center;
            transform: rotate(-35deg);
            font-size: 75pt;
            font-weight: bolder;
            text-transform: uppercase;
        }

        h2 { text-align: center; margin: 5px 0; }
        h2 { text-decoration: underline; font-weight: bold; }

        table { 
            width: 100%; 
            table-layout: fixed; /* This forces the 50/50 split you requested */
            border-collapse: collapse;
        }   
        th, td { border: 1px solid #000; padding: 5px; vertical-align: top; word-wrap: break-word; }

        /* Number column */

        /* Label and value equal width */
        .label, .value { width: calc(50% - 25px); }
        .label { 
            width: 45%; 
            font-weight: bold; 
        }
        .num { 
            width: 25px; 
            text-align: center; 
            vertical-align: middle;
        }
        .info-content { 
            margin-left: 10px;
            text-indent: 2em;
        }
        .value { 
            width: 45%; 
        }
        th { background-color: #f0f0f0; text-align: left; }

        .section-title {
            font-weight: bold;
            margin-top: 15px;
            margin-left: 10px;
            padding: 5px;
        }

        td p { margin: 0; }
        
        .attachments {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .attachment-img {
            width: 150px;
            object-fit: cover;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="watermark-wrapper">
        <div 
            class="watermark"
            style="color: {{ $bgColor }}"
        >
            {{-- COPY FOR {{ $copyFor ?? '' }} --}}
        </div>
    </div>

    <h3>A. Information Report/s Submitted</h3>
    <h2>INFORMATION REPORT</h2>

    <table>
        <tbody>
            <tr>
                <td class="num">1</td>
                <td class="label">FILE NUMBER</td>
                <td class="value">{{ $incident->file_number }}</td>
            </tr>
            <tr>
                <td class="num">2</td>
                <td class="label">REFERENCE</td>
                <td class="value">{{ $incident->reference }}</td>
            </tr>
            <tr>
                <td class="num">3</td>
                <td class="label">SUBJECT</td>
                <td class="value">{{ $incident->subject }}</td>
            </tr>
            <tr>
                <td class="num">4</td>
                <td class="label">DATE OF REPORT</td>
                <td class="value">{{ date('F d, Y', strtotime($incident->date_of_report)) }}</td>
            </tr>
            <tr>
                <td class="num">5</td>
                <td class="label">REPORTER</td>
                <td class="value">{{ $incident->reporter }}</td>
            </tr>
            <tr>
                <td class="num">6</td>
                <td class="label">DESIGNATION/UNIT ASSIGNMENT</td>
                <td class="value">{{ $incident->designation }}</td>
            </tr>
            <tr>
                <td class="num">7</td>
                <td class="label">EVALUATION</td>
                <td class="value">{{ $incident->evaluation }}</td>
            </tr>
            <tr>
                <td class="num">8</td>
                <td class="label">SOURCE</td>
                <td class="value">{{ $incident->source }}</td>
            </tr>
            <tr>
                <td class="num">9</td>
                <td class="label">DATE ACQUIRED</td>
                <td class="value">{{ date('F d, Y', strtotime($incident->date_acquired)) }}</td>
            </tr>
            <tr>
                <td class="num">10</td>
                <td class="label">MANNER ACQUIRED</td>
                <td class="value">{{ $incident->manner_acquired }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">11. INFORMATION PROPER</div>
    @php
        $paragraphs = preg_split("/\n\s*\n/", $incident->information_proper);
    @endphp
    @if(count($paragraphs))
        <p class="info-content">
            {{ $paragraphs[0] }}
        </p>

        @foreach(array_slice($paragraphs, 1) as $p)
            <p class="info-content">{{ $p }}</p>
        @endforeach
    @endif

    <table>
        <tbody>
            <tr>
                <td class="num">12</td>
                <td class="label">COMMENTS/ANALYSIS</td>
                <td class="value">{!! nl2br(e($incident->comments)) !!}</td>
            </tr>
            <tr>
                <td class="num">13</td>
                <td class="label">ACTION/s TAKEN/TO BE UNDERTAKEN/REQUESTED</td>
                <td class="value">{!! nl2br(e($incident->action_taken)) !!}</td>
            </tr>
            <tr>
                <td class="num">14</td>
                <td class="label">ATTACHMENT</td>
                <td class="value">
                    <div class="attachments">
                        @foreach ($attachments as $attachment)
                            <img     
                                src="{{ public_path('storage/incident/attachments/' . $attachment->file_name) }}" 
                                alt="Attachment"
                                class="attachment-img"
                            >
                        @endforeach
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>
