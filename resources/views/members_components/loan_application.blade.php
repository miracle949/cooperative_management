<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Loan Application — KPMPCATS</title>
    <link rel="icon" href="images/websitelogo.png" type="image/png">
    <link rel="stylesheet" href="css_folder/loan_application.css">
    <link rel="stylesheet" href="css_folder/loading.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/csp-events.js') }}"></script>
    <link rel="stylesheet" href="../font-awesome-icon/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Space+Grotesk:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --bg: #f2f4f8;
            --teal: #1E2A4A;
            --teal-dark: #131a30;
            --teal-mid: #263359;
            --teal-dark: #131a30;
            --teal-mid: #263359;
            --teal-pale: #F0F4FF;
            --gold: #F5A623;
            --blue: #4F7FFA;
            --blue-light: #95B3FC;
            --blue-pale: #D9E5FE;
            --blue-mist: #EEF3FF;
            --coral: #FF6B6B;
            --mint: #22C993;
            --cream: #F0F4FF;
            --ink: #1E2A4A;
            --muted: #6b7a99;
            --line: rgba(30, 42, 74, .1);
            --sidebar-width: 250px;
            --border: #E5EAF3;
            --border-hover: #D9E5FE;
            --sidebar-width: 250px;
            --accent: #2563eb;
            --mint: #22C993;
            --coral: #FF6B6B;
            --semi-white: #FAF9F6;
            --green: #1e9e6b;
            --green-bg: #e6f7f1;
            --savings: #1560c0;
            --savings-bg: #e4edff;
            --shadow: 0 2px 12px rgba(30, 42, 74, 0.08);

            --ice-blue: #E8F4FD;
            --gold-pale: #FFF8EC;
            --mint-pale: #EDFAF4;
            --coral-pale: #FFF0F0;
            --lavender-tint: #F7F8FC;
            --navy-soft: #2D3F6B;
        }

        .loan-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 1050;
            background: rgba(13, 15, 20, .6);
            backdrop-filter: blur(7px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            pointer-events: none;
            transition: opacity .3s ease;
        }

        .loan-modal-overlay.open {
            opacity: 1;
            pointer-events: all;
        }

        .loan-modal {
            background: #fff;
            border-radius: 24px;
            width: 100%;
            max-width: 820px;
            max-height: 94vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 32px 80px rgba(0, 0, 0, .28), 0 8px 24px rgba(0, 0, 0, .12);
            transform: translateY(28px) scale(.97);
            transition: transform .38s cubic-bezier(.22, 1, .36, 1);
            position: relative;
            font-family: 'Space Grotesk', sans-serif;
        }

        .loan-modal-overlay.open .loan-modal {
            transform: translateY(0) scale(1);
        }

        .modal-layout {
            display: flex;
            flex: 1;
            overflow: hidden;
            min-height: 0;
        }

        .modal-invoice-sidebar {
            width: 250px;
            flex-shrink: 0;
            background-color: #ffffff;
            border-right: 1px solid var(--border);
            padding: 26px 18px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            position: relative;
            overflow: hidden;
        }

        .mis-eyebrow {
            font-size: 8.5px;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--muted);
            position: relative;
            z-index: 1;
        }

        .mis-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--teal);
            position: relative;
            z-index: 1;
            margin-bottom: 1px;
        }

        .mis-sub {
            font-size: 10.5px;
            color: var(--muted);
            position: relative;
            z-index: 1;
        }

        .mis-amount-box {
            background-color: #fafafa;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .mis-amount-lbl {
            font-size: 8.5px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .mis-amount-val {
            font-size: 20px;
            font-weight: 700;
            color: var(--teal);
            line-height: 1;
        }

        .mis-amount-val.dim {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 20px;
        }

        .mis-amount-hint {
            font-size: 9.5px;
            color: var(--muted);
            margin-top: 5px;
        }

        .mis-rows {
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 1;
        }

        .mis-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, .07);
            font-size: 12.5px;
        }

        .mis-row:last-child {
            border-bottom: none;
        }

        .mis-lbl {
            color: var(--muted);
            font-weight: 600;
        }

        .mis-val {
            color: #1a1a1a;
            font-weight: 700;
            font-size: 12px;
        }

        .mis-val.dim {
            color: #1a1a1a;
            font-weight: 400;
        }

        .mis-divider {
            height: 1px;
            background-color: var(--border);
            margin: 4px 0;
            position: relative;
            z-index: 1;
        }

        .mis-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            position: relative;
            z-index: 1;
        }

        .mis-total-lbl {
            font-size: 12px;
            font-weight: 700;
            color: var(--muted);
        }

        .mis-total-val {
            font-size: 12px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .mis-total-val.dim {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 12px;
            font-weight: 400;
            color: var(--teal)
        }

        .modal-main-area {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .modal-steps-bar {
            display: flex;
            align-items: center;
            padding: 16px 24px;
            border-bottom: 1px solid var(--border);
            background-color: #ffffff;
            flex-shrink: 0;
        }

        .m-step {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
        }

        .m-step-circle {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            flex-shrink: 0;
            transition: .3s;
        }

        .m-step.active .m-step-circle {
            background: var(--teal);
            color: #ffffff;
            box-shadow: 0 0 0 3px #EDF0F5;
        }

        .m-step.done .m-step-circle {
            background: var(--teal);
            color: #ffffff;
        }

        .m-step.pending .m-step-circle {
            background-color: #fafafa;
            color: var(--light);
            border: 1px solid var(--border);
        }

        .m-step-info .m-step-num {
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            color: #1a1a1a;
        }

        .m-step.active .m-step-info .m-step-num {
            color: #1a1a1a;
        }

        .m-step.done .m-step-info .m-step-num {
            color: var(--forest);
        }

        .m-step-info .m-step-name {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--muted);
            margin-top: 1px;
        }

        .m-step.active .m-step-info .m-step-name {
            color: var(--ink);
        }

        .m-connector {
            flex: 0 0 28px;
            height: 2px;
            background: var(--border);
            margin: 0 4px;
            border-radius: 2px;
            transition: .4s;
        }

        .m-connector.done {
            background: var(--forest);
        }

        .modal-panel {
            display: none;
            padding: 24px 28px;
            animation: panelIn .3s ease both;
        }

        .modal-panel.active {
            display: block;
        }

        .modal-panel.back {
            animation: panelBack .3s ease both;
        }

        @keyframes panelIn {
            from {
                opacity: 0;
                transform: translateX(16px)
            }

            to {
                opacity: 1;
                transform: translateX(0)
            }
        }

        @keyframes panelBack {
            from {
                opacity: 0;
                transform: translateX(-16px)
            }

            to {
                opacity: 1;
                transform: translateX(0)
            }
        }

        .panel-sec-hd {
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--teal);
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .panel-sec-hd::before {
            content: '';
            width: 2.5px;
            height: 10px;
            border-radius: 2px;
            background: var(--teal);
            display: block;
        }

        .p-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 14px;
        }

        .p-form-row.single {
            grid-template-columns: 1fr;
        }

        .p-field {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .p-field label {
            font-size: 12px;
            font-weight: 600;
            color: var(--ink);
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .req {
            color: var(--danger);
        }

        .max-pill {
            font-size: 9.5px;
            font-weight: 700;
            padding: 1px 7px;
            border-radius: 8px;
            background-color: #EDF0F5;
            color: var(--teal);
        }

        .p-inp-wrap {
            position: relative;
        }

        .p-inp-ico {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            pointer-events: none;
        }

        .p-inp-ico svg {
            width: 13px;
            height: 13px;
        }

        .p-input,
        .p-select,
        .p-textarea {
            width: 100%;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 13px;
            color: var(--teal);
            background: #fff;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            padding: 10px 12px 10px 34px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            appearance: none;
        }

        .p-input.np,
        .p-select.np {
            padding-left: 12px;
        }

        .p-input::placeholder,
        .p-textarea::placeholder {
            color: #c4c0b8;
        }

        .p-input:focus,
        .p-select:focus,
        .p-textarea:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(79, 127, 250, .12);
        }

        .p-input.field-error,
        .p-select.field-error,
        .p-textarea.field-error {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, .1) !important;
        }

        .p-field-error {
            display: none;
            font-size: 11px;
            color: #dc2626;
            font-weight: 600;
            margin-top: 3px;
            align-items: center;
            gap: 4px;
        }

        .p-field-error.show {
            display: flex;
        }

        .p-sel-wrap {
            position: relative;
        }

        .p-sel-wrap::after {
            content: '';
            position: absolute;
            right: 11px;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            pointer-events: none;
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-top: 5px solid var(--muted);
        }

        .p-textarea {
            padding-left: 12px;
            resize: vertical;
            min-height: 78px;
            line-height: 1.6;
        }

        .p-hint {
            font-size: 11px;
            color: var(--muted);
        }

        .p-warn {
            display: none;
            margin-top: 6px;
            background: #fef2f2;
            border: 1.5px solid #fca5a5;
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 12px;
            color: #dc2626;
            font-weight: 500;
        }

        .sc-alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: #fdf9f0;
            border: 1.5px solid var(--warn-b);
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 20px;
        }

        .sc-alert-ico {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(232, 213, 163, .25);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sc-alert-ico svg {
            width: 16px;
            height: 16px;
            color: #b8862a;
        }

        .sc-alert-title {
            font-size: 13px;
            font-weight: 700;
            color: #7a5010;
            margin-bottom: 3px;
        }

        .sc-alert-text {
            font-size: 12px;
            color: #8a6020;
            line-height: 1.6;
        }

        .docs-section {
            display: none;
            margin-top: 8px;
        }

        .docs-heading {
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--teal);
            padding-bottom: 6px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .docs-heading::before {
            content: '';
            width: 2.5px;
            height: 10px;
            border-radius: 2px;
            background: var(--teal);
            display: block;
        }

        .upload-grid-modal {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .upload-card-modal {
            position: relative;
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 3px;
            cursor: pointer;
            transition: .2s;
            overflow: hidden;
        }

        .upload-card-modal:hover {
            border-color: var(--teal);
            background-color: #ffffff;
        }

        .upload-card-modal.has-file {
            border-color: var(--teal);
            background-color: #ffffff;
        }

        .upload-card-modal input[type=file] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .uc-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(27, 61, 47, .1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 6px;
        }

        .uc-icon svg {
            width: 14px;
            height: 14px;
            color: var(--forest);
        }

        .uc-label {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--ink);
        }

        .uc-sub {
            font-size: 10.5px;
            color: var(--muted);
        }

        .uc-badge {
            display: inline-block;
            margin-top: 3px;
            font-size: 9.5px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 6px;
        }

        .uc-badge.required {
            background: rgba(201, 68, 68, .1);
            color: var(--red);
        }

        .uc-badge.uploaded {
            background-color: var(--green-tint);
            color: var(--green);
        }

        .uc-badge.optional {
            background: var(--green-tint);
            color: var(--green);
        }

        .uc-filename {
            font-size: 10.5px;
            color: var(--success);
            font-weight: 600;
            margin-top: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .uc-error {
            font-size: 11px;
            color: var(--danger);
            font-weight: 600;
            margin-top: 2px;
            display: none;
        }

        .loan-status-banner {
            border-radius: 10px;
            padding: .85rem 1rem;
            margin: 1rem 0;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 12.5px;
        }

        .lsb-danger {
            background: #fef2f2;
            border: 1.5px solid #fca5a5;
            color: #dc2626;
        }

        .lsb-warn {
            background: #fff8e1;
            border: 1.5px solid #ffe082;
            color: #856404;
        }

        .lsb-ok {
            background: #f0fdf4;
            border: 1.5px solid #86efac;
            color: #166534;
        }

        .lsb-ico {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .lsb-ico.danger-ico {
            background: #fee2e2;
        }

        .lsb-ico.warn-ico {
            background: #fff3cd;
        }

        .lsb-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 3px;
        }

        .breakdown-grid-modal {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 18px;
        }

        .b-box-m {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 13px;
            transition: .2s;
        }

        .b-box-m.hl {
            background: var(--forest-pale);
            border-color: rgba(27, 61, 47, .15);
        }

        .b-ico-m {
            width: 30px;
            height: 30px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }

        .b-ico-m svg {
            width: 14px;
            height: 14px;
        }

        .bi-g {
            background: rgba(27, 61, 47, .1);
        }

        .bi-g svg {
            color: var(--forest);
        }

        .bi-o {
            background: rgba(184, 148, 63, .12);
        }

        .bi-o svg {
            color: var(--gold);
        }

        .bi-r {
            background: rgba(201, 68, 68, .1);
        }

        .bi-r svg {
            color: var(--danger);
        }

        .bi-b {
            background: rgba(59, 130, 246, .1);
        }

        .bi-b svg {
            color: #3b82f6;
        }

        .bi-p {
            background: rgba(139, 92, 246, .1);
        }

        .bi-p svg {
            color: #8b5cf6;
        }

        .bi-t {
            background: rgba(20, 184, 166, .1);
        }

        .bi-t svg {
            color: #14b8a6;
        }

        .bi-s {
            background: rgba(236, 72, 153, .1);
        }

        .bi-s svg {
            color: #ec4899;
        }

        .bi-n {
            background: rgba(34, 197, 94, .1);
        }

        .bi-n svg {
            color: #22c55e;
        }

        .b-lbl-m {
            font-size: 9.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--muted);
            margin-bottom: 2px;
        }

        .b-val-m {
            font-size: 17px;
            font-weight: 700;
            color: var(--ink);
        }

        .b-box-m.hl .b-val-m {
            color: var(--forest);
        }

        .b-hint-m {
            font-size: 10.5px;
            color: var(--muted);
            margin-top: 2px;
        }

        .amort-hd-m {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--teal);
            margin-bottom: 8px;
            display: none;
            align-items: center;
            gap: 6px;
        }

        .amort-hd-m::before {
            content: '';
            width: 2.5px;
            height: 10px;
            border-radius: 2px;
            background: var(--teal);
            display: block;
        }

        .amort-wrap-m {
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
            display: none;
            margin: 0 0 16px;
        }

        .amort-scroll-m {
            max-height: 200px;
            overflow-y: auto;
        }

        .amort-tbl {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .amort-tbl th {
            background: #f5f5f2;
            padding: 7px 10px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--muted);
            text-align: left;
            border-bottom: 2px solid var(--border);
        }

        .amort-tbl td {
            padding: 8px 10px;
            border-bottom: 1px solid var(--border);
        }

        .amort-tbl tr:last-child td {
            border-bottom: none;
        }

        .amort-tbl tr:hover td {
            background: #fafaf8;
        }

        .mo-n {
            font-weight: 700;
            color: var(--forest);
        }

        .int-n {
            color: var(--danger);
            font-weight: 600;
        }

        .bal-n {
            color: var(--muted);
        }

        .confirm-hero-m {
            text-align: center;
            padding: 8px 0 18px;
        }

        .confirm-ring-m {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: var(--teal);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
        }

        .confirm-ring-m .fa {
            color: #ffffff;
            font-size: 20px;
        }

        .confirm-ring-m svg {
            width: 22px;
            height: 22px;
            color: var(--forest);
        }

        .confirm-title-m {
            font-size: 21px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .confirm-sub-m {
            font-size: 12.5px;
            color: var(--muted);
            line-height: 1.65;
        }

        .sum-card-m {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            margin: 14px 0;
        }

        .sum-head-m {
            background: var(--teal);
            padding: 10px 14px;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #ffffff;
        }

        .sum-row-m {
            display: flex;
            justify-content: space-between;
            padding: 10px 14px;
            border-bottom: 1px solid var(--border);
            font-size: 12.5px;
        }

        .sum-row-m:last-child {
            border-bottom: none;
        }

        .sum-row-m.total {
            background-color: #ffffff;
        }

        .sum-row-m.subtle {
            background-color: #fafbfd;
        }

        .sum-lbl-m {
            color: var(--muted);
        }

        .sum-lbl-m.bold {
            color: var(--teal);
            font-weight: 700;
        }

        .sum-val-m {
            font-weight: 700;
            color: var(--teal);
        }

        .sum-val-m.green {
            color: var(--forest);
        }

        .sum-val-m.gold {
            color: var(--teal);
        }

        .sum-val-m.bigf {
            font-size: 12.5px;
            color: var(--teal);
        }

        .sum-val-m.deduct {
            color: var(--teal);
        }

        .cb-row-m {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 8px;
            margin-bottom: 16px;
            cursor: pointer;
            transition: border-color .2s;
        }

        .cb-row-m.cb-error {
            border-color: #dc2626;
            background: #fef2f2;
        }

        .cb-row-m input[type=checkbox] {
            width: 15px;
            height: 15px;
            margin-top: 2px;
            accent-color: var(--forest);
            flex-shrink: 0;
            cursor: pointer;
            padding: 0;
        }

        .cb-row-m label {
            font-size: 12px;
            color: var(--muted);
            line-height: 1.65;
            cursor: pointer;
        }

        .cb-row-m label strong {
            color: var(--ink);
        }

        .cb-required-msg {
            display: none;
            font-size: 11px;
            color: #dc2626;
            font-weight: 600;
            margin-top: 4px;
            align-items: center;
            gap: 4px;
        }

        .cb-required-msg.show {
            display: flex;
        }

        .modal-footer-bar {
            padding: 14px 20px;
            border-top: 1px solid var(--line);
            background: #fafafa;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-shrink: 0;
        }

        .mf-note {
            font-size: 11px;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .mf-note svg {
            width: 12px;
            height: 12px;
            color: var(--forest);
        }

        .mf-btns {
            display: flex;
            gap: 8px;
        }

        .m-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: .2s;
            white-space: nowrap;
        }

        .m-btn svg {
            width: 13px;
            height: 13px;
        }

        .m-btn-outline {
            background: transparent;
            color: var(--teal);
            border: 1px solid var(--teal)
        }

        .m-btn-outline:hover {
            border: 1px solid var(--teal)
        }

        .m-btn-primary {
            background: linear-gradient(135deg, var(--teal), var(--teal));
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(27, 61, 47, .22);
        }

        .m-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(27, 61, 47, .3);
        }

        .m-btn-gold {
            background: linear-gradient(135deg, var(--teal), var(--teal));
            color: #fff;
            box-shadow: 0 4px 14px rgba(27, 61, 47, .22);
        }

        .m-btn-gold:hover {
            transform: translateY(-1px);
        }

        .m-btn:disabled {
            opacity: .5;
            cursor: not-allowed;
            transform: none !important;
        }

        .success-screen-m {
            display: none;
            padding: 44px 32px;
            text-align: center;
            animation: fadeUp .5s ease both;
        }

        .success-screen-m.show {
            display: block;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .success-ring-m {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: rgba(30, 122, 78, .1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .success-ring-m svg {
            width: 30px;
            height: 30px;
            color: var(--success);
        }

        .success-title-m {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .success-sub-m {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.7;
            max-width: 360px;
            margin: 0 auto 20px;
        }

        .ref-pill-m {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--forest-pale);
            border: 1px solid rgba(27, 61, 47, .15);
            border-radius: 8px;
            padding: 9px 18px;
            font-size: 13px;
            font-weight: 700;
            color: var(--forest);
            margin-bottom: 22px;
        }

        .ref-pill-m svg {
            width: 14px;
            height: 14px;
        }

        @media (max-width: 768px) {
            .modal-invoice-sidebar {
                display: none;
            }

            .loan-modal {
                max-width: 560px;
            }

            .breakdown-grid-modal {
                grid-template-columns: 1fr 1fr;
            }

            .upload-grid-modal {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 500px) {
            .p-form-row {
                grid-template-columns: 1fr;
            }
        }

        .form-disabled-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, .7);
            border-radius: 24px;
            z-index: 5;
            cursor: not-allowed;
        }

        /* ══════════════════════════════════════════════════════════
            INTERACTIVE LOAN CARD LIST (replaces plain tables)
            ══════════════════════════════════════════════════════════ */
        .loan-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 0 20px 1.5rem;
        }

        .loan-card {
            border: 1px solid var(--line);
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
        }

        .loan-card:hover {
            border-color: var(--border-hover);
            box-shadow: 0 4px 16px rgba(30, 42, 74, .06);
        }

        .loan-ref-block {
            display: flex;
            align-items: center;
            gap: 15px;
            min-width: 0;
        }

        .loan-type-icon {
            width: 40px;
            height: 40px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background-color: #EDF0F5;
            color: var(--teal);
            font-size: 15px;
        }

        .loan-ref {
            font-weight: 700;
            font-size: 14px;
            color: #1a1a1a;
        }

        .loan-purpose {
            font-size: 14px;
            font-weight: 700;
            color: var(--teal);
        }

        .col-cell .cell-label {
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: var(--muted);
            font-weight: 700;
            margin-bottom: 3px;
        }

        .col-cell .cell-value {
            font-size: 13.5px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .loan-amount {
            font-size: 14.5px;
            font-weight: 800;
            color: #1a1a1a;
        }

        .badge-table.pending {
            background: #fff8e1;
            color: #b8860b;
        }

        .badge-table.pending::before {
            background: var(--gold);
        }

        .badge-table.approved {
            background-color: var(--mint-pale);
            color: var(--green);
        }

        .badge-table.approved::before {
            background: var(--mint);
        }

        .badge-table.completed {
            background-color: var(--green-tint);
            color: var(--green);
        }

        .badge-table.completed::before {
            background: var(--green);
        }

        .badge-table.rejected {
            background: var(--coral-pale);
            color: var(--coral);
        }

        .badge-table.rejected::before {
            background: var(--coral);
        }

        .badge-table.declined {
            background: #fef2f2;
            color: #dc2626;
        }

        .badge-table.declined::before {
            background: #dc2626;
        }

        .due-tag .fa {
            color: var(--mint);
        }

        .due-tag.today {
            background: var(--gold-pale);
            color: #a5710f;
        }

        .due-tag.week {
            background: var(--ice-blue);
            color: var(--savings);
        }

        .due-tag.late {
            background: var(--coral-pale);
            color: var(--coral);
        }

        .chevron {
            color: var(--muted);
            transition: transform .2s;
            flex-shrink: 0;
            display: flex;
            justify-content: end;
        }

        .loan-card.open .chevron {
            transform: rotate(180deg);
        }

        .loan-detail {
            max-height: 0;
            overflow: hidden;
            background: var(--lavender-tint);
            transition: max-height .3s ease;
        }

        .loan-card.open .loan-detail {
            max-height: 420px;
        }


        .detail-box {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 14px;
        }

        .detail-box .cell-label {
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: var(--muted);
            font-weight: 700;
            margin-bottom: 5px;
        }

        .loan-ref-block .cell-value {
            font-size: 12.5px;
            color: var(--muted);
            margin-top: 1px;
            font-weight: 600;
        }

        .detail-box .cell-value {
            font-size: 14px;
            font-weight: 700;
            color: var(--teal);
        }

        .progress-block {
            margin-bottom: 4px;
        }

        .progress-head {
            display: flex;
            justify-content: space-between;
            font-size: 12.5px;
            font-weight: 700;
            color: var(--teal-mid);
            margin-bottom: 7px;
        }

        .progress-track {
            height: 16px;
            background: var(--border);
            border-radius: 6px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 6px;
            background: linear-gradient(90deg, var(--teal), var(--teal));
            transition: width .6s ease;
        }

        .detail-actions {
            display: flex;
            gap: 10px;
            margin-top: 18px;
            flex-wrap: wrap;
        }

        .btn-ghost {
            background: #fff;
            border: 1px solid var(--border);
            color: var(--teal-mid);
            padding: 9px 16px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .btn-ghost:hover {
            border-color: var(--teal);
            color: var(--teal);
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--muted);
            font-size: 13px;
        }

        /* ============ LOCKED / GATED OVERLAY (Share Capital not met) ============ */
        .gated {
            position: relative;
        }

        .gated .gate-shield {
            position: absolute;
            inset: 0;
            background: rgba(247, 248, 252, .72);
            backdrop-filter: blur(3px);
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-align: center;
            padding: 20px;
            pointer-events: none;
            transition: opacity .18s ease;
            z-index: 5;
            border: 1px solid var(--line);
        }

        .gated:hover .gate-shield {
            opacity: 1;
            pointer-events: auto;
            border: 1px solid var(--line);
        }

        .gate-lock {
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--blue);
            font-size: 18px;
        }

        .gate-msg {
            font-size: 13px;
            font-weight: 700;
            color: var(--ink);
        }

        .gate-sub {
            font-size: 12.5px;
            color: var(--muted);
            max-width: 350px;
            line-height: 1.5;
        }

        .gated>*:not(.gate-shield) {
            filter: blur(2.5px);
            user-select: none;
        }
    </style>

    <script nonce="{{ csp_nonce() }}">
        function closeSuccessModal() {
            document.getElementById('success-modal').style.display = 'none';
        }
    </script>
</head>

<body>
    <div class="container-fluid m-0 p-0">
        @include("components.offcanvas")
        @include("components.sidebar")

        <!-- Interest Rates Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-text">
                            <h1>Interest Rates</h1>
                            <p>Per lending type - monthly basis</p>
                        </div>
                        <button type="button" data-bs-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="reminders">
                            <p>💡 Rates are indicative. Final terms subject to credit evaluation and cooperative
                                approval.</p>
                        </div>
                        <div class="lending-parent-box">
                            @foreach($loanSettings as $dbType => $s)
                                <div class="lending-parent">
                                    <div class="lending-icon">
                                        <p>{{ $dbType }}</p>
                                        <span>Applies to {{ $dbType }} applications</span>
                                    </div>
                                    <p>{{ rtrim(rtrim(number_format(($s['interest_rate'] ?? 0.02) * 100, 2), '0'), '.') }}%
                                        / mo</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <hr>
                    <div class="modal-footer">
                        <button type="button" class="btn" data-bs-dismiss="modal">Got it, Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="rightbar">
            @include("components.navbar2")
            @include("components.footer")

            <div class="main-sub-parent">
                <main>
                    <div class="main-parent">

                        <div class="table-header">
                            <div class="main-text">
                                <h3>Loan Application</h3>
                                <p>View your loan applications and track upcoming, due, and overdue payments</p>
                            </div>
                            <div class="{{ !$canApplyLoan ? 'gated' : '' }}">
                                <div class="main-button">
                                    <button data-action="openLoanModal" @if(!$canApplyLoan) disabled
                                    style="opacity:.5;cursor:not-allowed;" @endif>
                                        <i class="fa fa-plus"></i>
                                        <span>Apply for a Loan</span>
                                    </button>
                                </div>
                                @if(!$canApplyLoan)
                                    <div class="gate-shield">
                                        <div class="gate-lock"><i class="fa fa-lock"></i></div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Server-side validation feedback (e.g. net proceeds adjustment limits) --}}
                        @if(session('loan_blocked') || $errors->any())
                            <div
                                style="background:rgba(220,38,38,.06);border:1px solid #dc2626;color:#b91c1c;border-radius:10px;padding:12px 16px;font-size:13px;font-weight:600;margin-bottom:18px;display:flex;flex-direction:column;gap:4px;">
                                @if(session('loan_blocked'))
                                    <span><i class="fa fa-circle-exclamation"></i> {{ session('loan_blocked') }}</span>
                                @endif
                                @foreach($errors->all() as $error)
                                    <span><i class="fa fa-circle-exclamation"></i> {{ $error }}</span>
                                @endforeach
                            </div>
                        @endif

                        {{-- ══ STATS CARDS — its own gated/hover-lock block ══ --}}
                        <div class="{{ !$canApplyLoan ? 'gated' : '' }}">
                            <div class="card-box-parent">
                                <div class="card-box">
                                    <div class="card-header">
                                        <h5>All Loans</h5>
                                        <div class="card-icon"><i class="fa fa-file"></i></div>
                                    </div>
                                    <div class="card-body">
                                        <p>{{ $allLoansCount ?? 0 }}</p>
                                        <span>Total Application on file</span>
                                    </div>
                                </div>

                                <div class="card-box">
                                    <div class="card-header">
                                        <h5>Due Today</h5>
                                        <div class="card-icon"><i class="fa fa-triangle-exclamation"></i></div>
                                    </div>
                                    <div class="card-body">
                                        <p>{{ $dueTodayCount }}</p>
                                        <span>Payments due today</span>
                                    </div>
                                </div>

                                <div class="card-box">
                                    <div class="card-header">
                                        <h5>Due This Week</h5>
                                        <div class="card-icon"><i class="fa fa-calendar"></i></div>
                                    </div>
                                    <div class="card-body">
                                        <p>{{ $dueThisWeekCount }}</p>
                                        <span>Upcoming repayments</span>
                                    </div>
                                </div>

                                <div class="card-box">
                                    <div class="card-header">
                                        <h5>Overdue</h5>
                                        <div class="card-icon"><i class="fa fa-clock"></i></div>
                                    </div>
                                    <div class="card-body">
                                        <p>{{ $overdueCount }}</p>
                                        <span>Missed payments</span>
                                    </div>
                                </div>
                            </div>

                            @if(!$canApplyLoan)
                                <div class="gate-shield">
                                    <div class="gate-lock"><i class="fa fa-lock"></i></div>
                                    <div class="gate-msg">Loan application is locked</div>
                                    <div class="gate-sub">
                                        You need at least <strong>{{ number_format($minimumShares, 0) }}</strong> shares of
                                        share capital to apply for a loan.
                                        You currently have <strong>{{ number_format($currentShares, 2) }}</strong> shares.
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- ══ TABS + TABLE — its own separate gated/hover-lock block ══ --}}
                        <div class="{{ !$canApplyLoan ? 'gated' : '' }}" style="margin-top: 20px;">
                            <nav>
                                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                    <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab"
                                        data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home"
                                        aria-selected="true">
                                        All Loans
                                    </button>
                                    <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab"
                                        data-bs-target="#nav-profile" type="button" role="tab"
                                        aria-controls="nav-profile" aria-selected="false">
                                        Due Today
                                    </button>
                                    <button class="nav-link" id="nav-contact-tab" data-bs-toggle="tab"
                                        data-bs-target="#nav-contact" type="button" role="tab"
                                        aria-controls="nav-contact" aria-selected="false">
                                        Due This Week
                                    </button>
                                    <button class="nav-link" id="nav-disabled-tab" data-bs-toggle="tab"
                                        data-bs-target="#nav-disabled" type="button" role="tab"
                                        aria-controls="nav-disabled" aria-selected="false">
                                        Overdue
                                    </button>
                                </div>
                            </nav>

                            <div class="tab-content" id="nav-tabContent">

                                {{-- ══ TAB 1: ALL LOANS ══ --}}
                                <div class="tab-pane fade show active" id="nav-home" role="tabpanel"
                                    aria-labelledby="nav-home-tab" tabindex="0">
                                    <div class="table-parent">
                                        <div class="table-filter">
                                            <div class="filter search-parent">
                                                <i class="fa fa-search"></i>
                                                <input type="search" id="search-all" data-action="applyFilters"
                                                    data-arg='["all-loans-list","search-all","date-all","status-all"]'
                                                    data-trigger="input"
                                                    placeholder="Search by reference, type, purpose"
                                                    class="form-control">
                                            </div>

                                            <div class="filter-parent">
                                                <div class="filter date">
                                                    <input type="date" id="date-all" class="date-input"
                                                        data-action="applyFilters"
                                                        data-arg='["all-loans-list","search-all","date-all","status-all"]'
                                                        class="form-control">
                                                </div>

                                                <div class="filter status">
                                                    <select id="status-all" data-action="applyFilters"
                                                        data-arg='["all-loans-list","search-all","date-all","status-all"]'
                                                        class="form-select">
                                                        <option value="all">All Status</option>
                                                        <option value="Pending">Pending</option>
                                                        <option value="Approved">Approved</option>
                                                        <option value="Completed">Completed</option>
                                                        <option value="Rejected">Rejected</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="loan-list" id="all-loans-list">
                                            @forelse($allLoans ?? [] as $loan)
                                                @php
                                                    $statusKey = strtolower($loan->status);
                                                    $badgeClass = in_array($statusKey, ['pending', 'approved', 'completed', 'rejected', 'declined']) ? $statusKey : 'pending';
                                                    $progress = $loan->progress_percent ?? 0;
                                                    $dueCat = $loan->due_category ?? null;
                                                @endphp
                                                <div class="loan-card" data-ref="{{ $loan->reference_no }}"
                                                    data-status="{{ $loan->status }}"
                                                    data-date="{{ \Carbon\Carbon::parse($loan->created_at)->format('Y-m-d') }}">
                                                    <div class="loan-row" data-action="toggleLoanCard" data-arg='["|el|"]'>
                                                        <div class="loan-ref-block">
                                                            <div class="loan-type-icon"><i class="fa fa-file-lines"></i>
                                                            </div>
                                                            <div>
                                                                <div class="loan-ref">{{ $loan->reference_no }}</div>
                                                                <div class="cell-value">{{ $loan->lending_type }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-cell col-purpose-cell">
                                                            <div class="cell-label">Date Applied</div>
                                                            <div class="cell-value">
                                                                {{ \Carbon\Carbon::parse($loan->created_at)->format('M d, Y') }}
                                                            </div>
                                                        </div>
                                                        <div class="col-cell col-term">
                                                            <div class="cell-label">Term</div>
                                                            <div class="cell-value">{{ $loan->lending_type_term }}</div>
                                                        </div>
                                                        <div class="col-cell">
                                                            <div class="cell-label">Loan Amount</div>
                                                            <div class="loan-amount">
                                                                ₱{{ number_format($loan->lending_amount, 2) }}</div>
                                                        </div>
                                                        <div class="col-status">
                                                            <span
                                                                class="badge-table {{ $badgeClass }}">{{ $loan->status }}</span>
                                                            @if($loan->status === 'Approved')
                                                                <span class="due-tag"
                                                                    style="background:var(--mint-pale);color:var(--green);">
                                                                    <i class="fa fa-circle" style="font-size:6px;"></i>
                                                                    Active
                                                                </span>
                                                            @endif
                                                            @if($dueCat === 'today')<span class="due-tag today">Due
                                                            Today</span>@endif
                                                            @if($dueCat === 'week')<span class="due-tag week">Due This
                                                            Week</span>@endif
                                                            @if($dueCat === 'overdue')<span
                                                            class="due-tag late">Overdue</span>@endif
                                                        </div>
                                                        <svg class="chevron" width="18" height="18" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M6 9l6 6 6-6" />
                                                        </svg>
                                                    </div>

                                                    <div class="loan-detail">
                                                        <div class="loan-detail-inner">
                                                            <div class="detail-tabs">
                                                                <button type="button" class="detail-tab active"
                                                                    data-action="switchDetailTab"
                                                                    data-arg='["|el|","summary"]'>Summary</button>
                                                                <button type="button" class="detail-tab"
                                                                    data-action="switchDetailTab"
                                                                    data-arg='["|el|","details"]'>Loan Details</button>
                                                            </div>

                                                            <div class="detail-pane active" data-pane="summary">
                                                                <div class="detail-summary-grid">
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Date Applied</div>
                                                                        <div class="cell-value">
                                                                            {{ \Carbon\Carbon::parse($loan->created_at)->format('M d, Y') }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Loan Amount</div>
                                                                        <div class="cell-value">
                                                                            ₱{{ number_format($loan->lending_amount, 2) }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Status</div>
                                                                        <div class="col-status">
                                                                            <span
                                                                                class="badge-table {{ $badgeClass }}">{{ $loan->status }}</span>
                                                                            @if($loan->status === 'Approved')
                                                                                <span class="due-tag"
                                                                                    style="background:var(--mint-pale);color:var(--green);">Active</span>
                                                                            @endif
                                                                            @if($dueCat === 'today')<span
                                                                            class="due-tag today">Due Today</span>@endif
                                                                            @if($dueCat === 'week')<span
                                                                                class="due-tag week">Due This
                                                                            Week</span>@endif
                                                                            @if($dueCat === 'overdue')<span
                                                                            class="due-tag late">Overdue</span>@endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="detail-pane" data-pane="details">
                                                                <div class="detail-grid">
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Purpose of Loan</div>
                                                                        <div class="loan-purpose">{{ $loan->purpose_loan }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Term</div>
                                                                        <div class="cell-value">
                                                                            {{ $loan->lending_type_term }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Per Installment</div>
                                                                        <div class="cell-value">
                                                                            ₱{{ number_format($loan->monthly_payment ?? 0, 2) }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Next Due</div>
                                                                        <div class="cell-value">
                                                                            {{ ($loan->due_date && ($loan->remaining_balance ?? 0) > 0) ? \Carbon\Carbon::parse($loan->due_date)->format('M d, Y') : '—' }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Total Charges</div>
                                                                        <div class="cell-value">
                                                                            ₱{{ number_format($loan->total_charges ?? 0, 2) }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Total Payable</div>
                                                                        <div class="cell-value">
                                                                            ₱{{ number_format($loan->total_payable ?? 0, 2) }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if(($loan->total_payments ?? 0) > 0)
                                                                <div class="progress-block">
                                                                    <div class="progress-head">
                                                                        <span>Repayment Progress</span>
                                                                        <span>{{ $loan->payments_made ?? 0 }} of
                                                                            {{ $loan->total_payments ?? 0 }} installments ·
                                                                            {{ $loan->progress_percent ?? 0 }}%</span>
                                                                    </div>
                                                                    <div class="progress-track">
                                                                        <div class="progress-fill"
                                                                            style="width:{{ $loan->progress_percent ?? 0 }}%">
                                                                        </div>
                                                                    </div>
                                                                    @if($loan->status === 'Completed')
                                                                        <div
                                                                            style="margin-top:8px;font-size:11.5px;color:var(--green);font-weight:600;">
                                                                            <i class="fa fa-circle-check"></i>
                                                                            This loan has been fully paid off. Great job!
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <div class="detail-box"
                                                                    style="text-align:center; padding: 14px;">
                                                                    <div class="cell-value"
                                                                        style="font-weight:600; color:var(--muted);">
                                                                        @if($loan->status === 'Pending')
                                                                            Your application is awaiting review by the credit
                                                                            committee.
                                                                        @elseif($loan->status === 'Rejected')
                                                                            This application was not approved.
                                                                        @else
                                                                            A repayment schedule will be generated once funds are
                                                                            released.
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            <div class="detail-actions">
                                                                @if(in_array($loan->status, ['Approved', 'Completed']))
                                                                    <a class="btn-ghost"
                                                                        href="{{ route('LoanStatus', ['loan_id' => $loan->id]) }}"
                                                                        data-action="stop-propagation">
                                                                        <i class="fa fa-calendar-check"></i> View Repayment
                                                                        Schedule
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="empty-state">
                                                    <i class="fa fa-folder-open"
                                                        style="font-size:24px;display:block;margin-bottom:8px;opacity:.4;"></i>
                                                    No loan applications yet.
                                                </div>
                                            @endforelse
                                        </div>

                                        <div class="parent-pagination">
                                            <p>Showing <span
                                                    id="all-loans-list-shown">{{ count($allLoans ?? []) }}</span>
                                                of
                                                <span>{{ $allLoansCount ?? 0 }}</span>
                                            </p>
                                            <div class="pg-controls" id="all-loans-list-pg"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ══ TAB 2: DUE TODAY ══ --}}
                                <div class="tab-pane fade" id="nav-profile" role="tabpanel"
                                    aria-labelledby="nav-profile-tab" tabindex="0">
                                    <div class="table-parent">
                                        <div class="table-filter">
                                            <div class="filter search-parent">
                                                <i class="fa fa-search"></i>
                                                <input type="search" id="search-today" data-action="applyFilters"
                                                    data-arg='["due-today-list","search-today","date-today","status-today"]'
                                                    data-trigger="input"
                                                    placeholder="Search by reference, type, purpose">
                                            </div>

                                            <div class="filter-parent">
                                                <div class="filter date">
                                                    <input type="date" id="date-today" class="date-input"
                                                        data-action="applyFilters"
                                                        data-arg='["due-today-list","search-today","date-today","status-today"]'
                                                        class="form-control">
                                                </div>

                                                <div class="filter status">
                                                    <select id="status-today" data-action="applyFilters"
                                                        data-arg='["due-today-list","search-today","date-today","status-today"]'
                                                        class="form-select">
                                                        <option value="all">All Status</option>
                                                        <option value="Pending">Pending</option>
                                                        <option value="Approved">Approved</option>
                                                        <option value="Completed">Completed</option>
                                                        <option value="Rejected">Rejected</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="loan-list" id="due-today-list">
                                            @forelse($dueTodayLoans ?? [] as $loan)
                                                @php $progress = ($loan->total_payments ?? 0) > 0 ? min(100, round((($loan->payments_made ?? 0) / $loan->total_payments) * 100)) : 0; @endphp
                                                <div class="loan-card" data-ref="{{ $loan->reference_no }}"
                                                    data-status="{{ $loan->status }}"
                                                    data-date="{{ \Carbon\Carbon::parse($loan->created_at)->format('Y-m-d') }}">
                                                    <div class="loan-row" data-action="toggleLoanCard" data-arg='["|el|"]'>
                                                        <div class="loan-ref-block">
                                                            <div class="loan-type-icon"><i class="fa fa-file-lines"></i>
                                                            </div>
                                                            <div style="min-width:0;">
                                                                <div class="loan-ref">{{ $loan->reference_no }}</div>
                                                                <div class="cell-value">{{ $loan->lending_type }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-cell col-purpose-cell">
                                                            <div class="cell-label">Date Applied</div>
                                                            <div class="cell-value">
                                                                {{ \Carbon\Carbon::parse($loan->created_at)->format('M d, Y') }}
                                                            </div>
                                                        </div>
                                                        <div class="col-cell col-term">
                                                            <div class="cell-label">Term</div>
                                                            <div class="cell-value">{{ $loan->lending_type_term }}</div>
                                                        </div>
                                                        <div class="col-cell">
                                                            <div class="cell-label">Amount</div>
                                                            <div class="loan-amount">
                                                                ₱{{ number_format($loan->lending_amount, 2) }}</div>
                                                        </div>
                                                        <div class="col-status">
                                                            <span class="badge-table approved">Active</span>
                                                            <span class="due-tag today">Due Today</span>
                                                        </div>
                                                        <svg class="chevron" width="18" height="18" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M6 9l6 6 6-6" />
                                                        </svg>
                                                    </div>
                                                    <div class="loan-detail">
                                                        <div class="loan-detail-inner">
                                                            <div class="detail-tabs">
                                                                <button type="button" class="detail-tab active"
                                                                    data-action="switchDetailTab"
                                                                    data-arg='["|el|","summary"]'>Summary</button>
                                                                <button type="button" class="detail-tab"
                                                                    data-action="switchDetailTab"
                                                                    data-arg='["|el|","details"]'>Loan Details</button>
                                                            </div>

                                                            <div class="detail-pane active" data-pane="summary">
                                                                <div class="detail-summary-grid">
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Date Applied</div>
                                                                        <div class="cell-value">
                                                                            {{ \Carbon\Carbon::parse($loan->created_at)->format('M d, Y') }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Loan Amount</div>
                                                                        <div class="cell-value">
                                                                            ₱{{ number_format($loan->lending_amount, 2) }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Status</div>
                                                                        <div class="col-status">
                                                                            <span class="badge-table approved">Active</span>
                                                                            <span class="due-tag today">Due Today</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="detail-pane" data-pane="details">
                                                                <div class="detail-grid">
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Purpose of Loan</div>
                                                                        <div class="loan-purpose">{{ $loan->purpose_loan }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Term</div>
                                                                        <div class="cell-value">
                                                                            {{ $loan->lending_type_term }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Monthly Payment</div>
                                                                        <div class="cell-value">
                                                                            ₱{{ number_format($loan->monthly_payment ?? 0, 2) }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Due Date</div>
                                                                        <div class="cell-value">
                                                                            {{ $loan->due_date ? \Carbon\Carbon::parse($loan->due_date)->format('M d, Y') : '—' }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Total Charges</div>
                                                                        <div class="cell-value">
                                                                            ₱{{ number_format($loan->total_charges ?? 0, 2) }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Total Payable</div>
                                                                        <div class="cell-value">
                                                                            ₱{{ number_format($loan->total_payable ?? 0, 2) }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="progress-block">
                                                                <div class="progress-head">
                                                                    <span>Repayment Progress</span>
                                                                    <span>{{ $loan->payments_made ?? 0 }} of
                                                                        {{ $loan->total_payments ?? 0 }} installments ·
                                                                        {{ $progress }}%</span>
                                                                </div>
                                                                <div class="progress-track">
                                                                    <div class="progress-fill"
                                                                        style="width:{{ $progress }}%">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="detail-actions">
                                                                <a class="btn-ghost"
                                                                    href="{{ route('LoanStatus', ['loan_id' => $loan->id]) }}"
                                                                    data-action="stop-propagation">
                                                                    <i class="fa fa-calendar-check"></i> View Repayment
                                                                    Schedule
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="empty-state">
                                                    <i class="fa fa-calendar-check"
                                                        style="font-size:24px;display:block;margin-bottom:8px;opacity:.4;"></i>
                                                    No payments due today.
                                                </div>
                                            @endforelse
                                        </div>

                                        <div class="parent-pagination">
                                            <p>Showing <span>{{ count($dueTodayLoans ?? []) }}</span> of
                                                <span>{{ $dueTodayCount ?? 0 }}</span> records
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- ══ TAB 3: DUE THIS WEEK ══ --}}
                                <div class="tab-pane fade" id="nav-contact" role="tabpanel"
                                    aria-labelledby="nav-contact-tab" tabindex="0">
                                    <div class="table-parent">
                                        <div class="table-filter">
                                            <div class="filter search-parent">
                                                <i class="fa fa-search"></i>
                                                <input type="search" id="search-week" data-action="applyFilters"
                                                    data-arg='["due-week-list","search-week","date-week","status-week"]'
                                                    data-trigger="input"
                                                    placeholder="Search by reference, type, purpose">
                                            </div>

                                            <div class="filter-parent">
                                                <div class="filter date">
                                                    <input type="date" id="date-week" class="date-input"
                                                        data-action="applyFilters"
                                                        data-arg='["due-week-list","search-week","date-week","status-week"]'
                                                        class="form-control">
                                                </div>

                                                <div class="filter status">
                                                    <select id="status-week" data-action="applyFilters"
                                                        data-arg='["due-week-list","search-week","date-week","status-week"]'
                                                        class="form-select">
                                                        <option value="all">All Status</option>
                                                        <option value="Pending">Pending</option>
                                                        <option value="Approved">Approved</option>
                                                        <option value="Completed">Completed</option>
                                                        <option value="Rejected">Rejected</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="loan-list" id="due-week-list">
                                            @forelse($dueThisWeekLoans ?? [] as $loan)
                                                @php $progress = ($loan->total_payments ?? 0) > 0 ? min(100, round((($loan->payments_made ?? 0) / $loan->total_payments) * 100)) : 0; @endphp
                                                <div class="loan-card" data-ref="{{ $loan->reference_no }}"
                                                    data-status="{{ $loan->status }}"
                                                    data-date="{{ \Carbon\Carbon::parse($loan->created_at)->format('Y-m-d') }}">
                                                    <div class="loan-row" data-action="toggleLoanCard" data-arg='["|el|"]'>
                                                        <div class="loan-ref-block">
                                                            <div class="loan-type-icon"><i class="fa fa-file-lines"></i>
                                                            </div>
                                                            <div style="min-width:0;">
                                                                <div class="loan-ref">{{ $loan->reference_no }}</div>
                                                                <div class="cell-value">{{ $loan->lending_type }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-cell col-purpose-cell">
                                                            <div class="cell-label">Date Applied</div>
                                                            <div class="cell-value">
                                                                {{ \Carbon\Carbon::parse($loan->created_at)->format('M d, Y') }}
                                                            </div>
                                                        </div>
                                                        <div class="col-cell col-term">
                                                            <div class="cell-label">Term</div>
                                                            <div class="cell-value">{{ $loan->lending_type_term }}</div>
                                                        </div>
                                                        <div class="col-cell">
                                                            <div class="cell-label">Amount</div>
                                                            <div class="loan-amount">
                                                                ₱{{ number_format($loan->lending_amount, 2) }}</div>
                                                        </div>
                                                        <div class="col-status">
                                                            <span class="badge-table approved">Active</span>
                                                            <span class="due-tag week">Due This Week</span>
                                                        </div>
                                                        <svg class="chevron" width="18" height="18" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M6 9l6 6 6-6" />
                                                        </svg>
                                                    </div>
                                                    <div class="loan-detail">
                                                        <div class="loan-detail-inner">
                                                            <div class="detail-tabs">
                                                                <button type="button" class="detail-tab active"
                                                                    data-action="switchDetailTab"
                                                                    data-arg='["|el|","summary"]'>Summary</button>
                                                                <button type="button" class="detail-tab"
                                                                    data-action="switchDetailTab"
                                                                    data-arg='["|el|","details"]'>Loan Details</button>
                                                            </div>

                                                            <div class="detail-pane active" data-pane="summary">
                                                                <div class="detail-summary-grid">
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Date Applied</div>
                                                                        <div class="cell-value">
                                                                            {{ \Carbon\Carbon::parse($loan->created_at)->format('M d, Y') }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Loan Amount</div>
                                                                        <div class="cell-value">
                                                                            ₱{{ number_format($loan->lending_amount, 2) }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Status</div>
                                                                        <div class="col-status">
                                                                            <span class="badge-table approved">Active</span>
                                                                            <span class="due-tag week">Due This Week</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="detail-pane" data-pane="details">
                                                                <div class="detail-grid">
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Purpose of Loan</div>
                                                                        <div class="loan-purpose">{{ $loan->purpose_loan }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Term</div>
                                                                        <div class="cell-value">
                                                                            {{ $loan->lending_type_term }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Monthly Payment</div>
                                                                        <div class="cell-value">
                                                                            ₱{{ number_format($loan->monthly_payment ?? 0, 2) }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Due Date</div>
                                                                        <div class="cell-value">
                                                                            {{ $loan->due_date ? \Carbon\Carbon::parse($loan->due_date)->format('M d, Y') : '—' }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Total Charges</div>
                                                                        <div class="cell-value">
                                                                            ₱{{ number_format($loan->total_charges ?? 0, 2) }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Total Payable</div>
                                                                        <div class="cell-value">
                                                                            ₱{{ number_format($loan->total_payable ?? 0, 2) }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="progress-block">
                                                                <div class="progress-head">
                                                                    <span>Repayment Progress</span>
                                                                    <span>{{ $loan->payments_made ?? 0 }} of
                                                                        {{ $loan->total_payments ?? 0 }} installments ·
                                                                        {{ $progress }}%</span>
                                                                </div>
                                                                <div class="progress-track">
                                                                    <div class="progress-fill"
                                                                        style="width:{{ $progress }}%">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="detail-actions">
                                                                <a class="btn-ghost"
                                                                    href="{{ route('LoanStatus', ['loan_id' => $loan->id]) }}"
                                                                    data-action="stop-propagation">
                                                                    <i class="fa fa-calendar-check"></i> View Repayment
                                                                    Schedule
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="empty-state">
                                                    <i class="fa fa-calendar"
                                                        style="font-size:24px;display:block;margin-bottom:8px;opacity:.4;"></i>
                                                    No upcoming payments this week.
                                                </div>
                                            @endforelse
                                        </div>

                                        <div class="parent-pagination">
                                            <p>Showing <span>{{ count($dueThisWeekLoans ?? []) }}</span> of
                                                <span>{{ $dueThisWeekCount ?? 0 }}</span> records
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- ══ TAB 4: OVERDUE ══ --}}
                                <div class="tab-pane fade" id="nav-disabled" role="tabpanel"
                                    aria-labelledby="nav-disabled-tab" tabindex="0">
                                    <div class="table-parent">
                                        <div class="table-filter">
                                            <div class="filter search-parent">
                                                <i class="fa fa-search"></i>
                                                <input type="search" id="search-overdue" data-action="applyFilters"
                                                    data-arg='["overdue-list","search-overdue","date-overdue","status-overdue"]'
                                                    data-trigger="input"
                                                    placeholder="Search by reference, type, purpose">
                                            </div>

                                            <div class="filter-parent">
                                                <div class="filter date">
                                                    <input type="date" id="date-overdue" class="date-input"
                                                        data-action="applyFilters"
                                                        data-arg='["overdue-list","search-overdue","date-overdue","status-overdue"]'
                                                        class="form-control">
                                                </div>

                                                <div class="filter status">
                                                    <select id="status-overdue" data-action="applyFilters"
                                                        data-arg='["overdue-list","search-overdue","date-overdue","status-overdue"]'
                                                        class="form-select">
                                                        <option value="all">All Status</option>
                                                        <option value="Pending">Pending</option>
                                                        <option value="Approved">Approved</option>
                                                        <option value="Completed">Completed</option>
                                                        <option value="Rejected">Rejected</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="loan-list" id="overdue-list">
                                            @forelse($overdueLoans ?? [] as $loan)
                                                @php $progress = ($loan->total_payments ?? 0) > 0 ? min(100, round((($loan->payments_made ?? 0) / $loan->total_payments) * 100)) : 0; @endphp
                                                <div class="loan-card" data-ref="{{ $loan->reference_no }}"
                                                    data-status="{{ $loan->status }}"
                                                    data-date="{{ \Carbon\Carbon::parse($loan->created_at)->format('Y-m-d') }}">
                                                    <div class="loan-row" data-action="toggleLoanCard" data-arg='["|el|"]'>
                                                        <div class="loan-ref-block">
                                                            <div class="loan-type-icon"><i class="fa fa-file-lines"></i>
                                                            </div>
                                                            <div style="min-width:0;">
                                                                <div class="loan-ref">{{ $loan->reference_no }}</div>
                                                                <div class="cell-value">{{ $loan->lending_type }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-cell col-purpose-cell">
                                                            <div class="cell-label">Date Applied</div>
                                                            <div class="cell-value">
                                                                {{ \Carbon\Carbon::parse($loan->created_at)->format('M d, Y') }}
                                                            </div>
                                                        </div>
                                                        <div class="col-cell col-term">
                                                            <div class="cell-label">Term</div>
                                                            <div class="cell-value">{{ $loan->lending_type_term }}</div>
                                                        </div>
                                                        <div class="col-cell">
                                                            <div class="cell-label">Amount</div>
                                                            <div class="loan-amount">
                                                                ₱{{ number_format($loan->lending_amount, 2) }}</div>
                                                        </div>
                                                        <div class="col-status">
                                                            <span class="badge-table approved">Active</span>
                                                            <span class="due-tag late">Overdue</span>
                                                        </div>
                                                        <svg class="chevron" width="18" height="18" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M6 9l6 6 6-6" />
                                                        </svg>
                                                    </div>
                                                    <div class="loan-detail">
                                                        <div class="loan-detail-inner">
                                                            <div class="detail-tabs">
                                                                <button type="button" class="detail-tab active"
                                                                    data-action="switchDetailTab"
                                                                    data-arg='["|el|","summary"]'>Summary</button>
                                                                <button type="button" class="detail-tab"
                                                                    data-action="switchDetailTab"
                                                                    data-arg='["|el|","details"]'>Loan Details</button>
                                                            </div>

                                                            <div class="detail-pane active" data-pane="summary">
                                                                <div class="detail-summary-grid">
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Date Applied</div>
                                                                        <div class="cell-value">
                                                                            {{ \Carbon\Carbon::parse($loan->created_at)->format('M d, Y') }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Loan Amount</div>
                                                                        <div class="cell-value">
                                                                            ₱{{ number_format($loan->lending_amount, 2) }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Status</div>
                                                                        <div class="col-status">
                                                                            <span class="badge-table approved">Active</span>
                                                                            <span class="due-tag late">Overdue</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="detail-pane" data-pane="details">
                                                                <div class="detail-grid">
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Purpose of Loan</div>
                                                                        <div class="loan-purpose">{{ $loan->purpose_loan }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Term</div>
                                                                        <div class="cell-value">
                                                                            {{ $loan->lending_type_term }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Monthly Payment</div>
                                                                        <div class="cell-value">
                                                                            ₱{{ number_format($loan->monthly_payment ?? 0, 2) }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Due Date</div>
                                                                        <div class="cell-value">
                                                                            {{ $loan->due_date ? \Carbon\Carbon::parse($loan->due_date)->format('M d, Y') : '—' }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Total Charges</div>
                                                                        <div class="cell-value">
                                                                            ₱{{ number_format($loan->total_charges ?? 0, 2) }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="detail-box">
                                                                        <div class="cell-label">Total Payable</div>
                                                                        <div class="cell-value">
                                                                            ₱{{ number_format($loan->total_payable ?? 0, 2) }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="progress-block">
                                                                <div class="progress-head">
                                                                    <span>Repayment Progress</span>
                                                                    <span>{{ $loan->payments_made ?? 0 }} of
                                                                        {{ $loan->total_payments ?? 0 }} installments ·
                                                                        {{ $progress }}%</span>
                                                                </div>
                                                                <div class="progress-track">
                                                                    <div class="progress-fill"
                                                                        style="width:{{ $progress }}%">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="detail-actions">
                                                                <a class="btn-ghost"
                                                                    href="{{ route('LoanStatus', ['loan_id' => $loan->id]) }}"
                                                                    data-action="stop-propagation">
                                                                    <i class="fa fa-calendar-check"></i> View Repayment
                                                                    Schedule
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="empty-state">
                                                    <i class="fa fa-circle-check"
                                                        style="font-size:24px;display:block;margin-bottom:8px;opacity:.4;color:#1e9e6b;"></i>
                                                    No overdue payments. Great job!
                                                </div>
                                            @endforelse
                                        </div>

                                        <div class="parent-pagination">
                                            <p>Showing <span>{{ count($overdueLoans ?? []) }}</span> of
                                                <span>{{ $overdueCount ?? 0 }}</span> records
                                            </p>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            @if(!$canApplyLoan)
                                <div class="gate-shield">
                                    <div class="gate-lock"><i class="fa fa-lock"></i></div>
                                    <div class="gate-msg">Loan history is locked</div>
                                    <div class="gate-sub">
                                        @if($loanEligSettings->savings_to_loan_enabled)
                                            You need savings of at least the loan amount plus the
                                            ₱{{ number_format($loanEligSettings->savings_to_loan_ratio, 2) }} holdback to apply.
                                            You currently have <strong>₱{{ number_format($currentSavings, 2) }}</strong> in
                                            savings.
                                        @else
                                            Please contact the administrator to apply for a loan.
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Card expand/collapse + search filter JS --}}
                        <script nonce="{{ csp_nonce() }}">
                            (function () {
                                var A = window.CSP_actions;
                                if (!A) return;
                                A.register('m-term-change', function (e, el) { mUpdateTermOptions(); mCompute(); mClearError(el); });
                                A.register('m-amount-input', function (e, el) { let v = parseFloat(el.value); if (v > 25000) el.value = 25000; mCheckLimit(el); mCompute(); mClearError(el); });
                                A.register('m-term-sync', function (e, el) { mSyncTerm(); mCompute(); mClearError(el); });
                                A.register('m-income-input', function (e, el) { if (el.value.length > 6) el.value = el.value.slice(0, 6); mClearError(el); });
                                A.register('m-purpose-change', function (e, el) { mHandlePurpose(el); mClearError(el); });
                                A.register('m-adjust-change', function (e, el) { mHandleAdjustType(el); mCompute(); mClearError(el); });
                                A.register('m-agree-row-click', function (e, el) { document.getElementById('mAgree').click(); });
                                A.register('m-agree-check', function (e, el) { mClearAgreeError(); });
                                A.register('m-dashboard-back', function (e, el) { closeLoanModal(); location.reload(); });
                            })();

                            function toggleLoanCard(rowEl) {
                                const card = rowEl.closest('.loan-card');
                                if (!card) return;
                                card.classList.toggle('open');
                            }

                            function switchDetailTab(btn, pane) {
                                const inner = btn.closest('.loan-detail-inner');
                                if (!inner) return;
                                inner.querySelectorAll('.detail-tab').forEach(b => b.classList.toggle('active', b === btn));
                                inner.querySelectorAll('.detail-pane').forEach(p => p.classList.toggle('active', p.dataset.pane === pane));
                            }

                            function filterCards(listId, query) {
                                const list = document.getElementById(listId);
                                if (!list) return;
                                const q = query.toLowerCase().trim();
                                list.querySelectorAll('.loan-card').forEach(card => {
                                    card.style.display = (q === '' || card.textContent.toLowerCase().includes(q)) ? '' : 'none';
                                });
                            }
                        </script>

                    </div>
                </main>
            </div>
        </div>

        <!-- LOAN APPLICATION MODAL -->
        <div class="loan-modal-overlay" id="loanModalOverlay" data-action="maybeCloseLoanModal" data-arg='["|event|"]'>
            <div class="loan-modal" id="loanModal">
                <div class="modal-accent-bar"></div>

                <div class="modal-layout">
                    <!-- Live Invoice Sidebar -->
                    <div class="modal-invoice-sidebar">
                        <div style="position:relative;z-index:1;" class="d-flex flex-column align-items-start gap-1">
                            <div class="mis-eyebrow">Live Preview</div>
                            <div class="mis-title">Loan Invoice</div>
                            <div class="mis-sub">Updates as you fill</div>
                        </div>
                        <div class="mis-amount-box">
                            <div class="mis-amount-lbl">Average Monthly Payment</div>
                            <div class="mis-amount-val dim" id="mis-mo">—</div>
                            <div class="mis-amount-hint" id="mis-hint">Enter details to compute</div>
                        </div>
                        <div class="mis-rows">
                            <div class="mis-row"><span class="mis-lbl">Type</span><span class="mis-val dim"
                                    id="mis-type">—</span></div>
                            <div class="mis-row"><span class="mis-lbl">Amount</span><span class="mis-val dim"
                                    id="mis-amount">—</span></div>
                            <div class="mis-row"><span class="mis-lbl">Rate</span><span class="mis-val dim"
                                    id="mis-rate">—</span></div>
                            <div class="mis-row"><span class="mis-lbl">Term</span><span class="mis-val dim"
                                    id="mis-term">—</span></div>
                            <div class="mis-row"><span class="mis-lbl">Interest</span><span class="mis-val dim"
                                    id="mis-int">—</span></div>
                            <div class="mis-row"><span class="mis-lbl">Total Charges</span><span class="mis-val dim"
                                    id="mis-totfees">—</span></div>
                            <div class="mis-row"><span class="mis-lbl">Net Proceeds</span><span class="mis-val dim"
                                    id="mis-net">—</span></div>
                        </div>
                        <div class="mis-divider"></div>
                        <div class="mis-total-row">
                            <span class="mis-total-lbl">Total Payable</span>
                            <span class="mis-total-val dim" id="mis-total">—</span>
                        </div>
                    </div>

                    <!-- Main Modal Content -->
                    <div class="modal-main-area">

                        <!-- Step Indicator -->
                        <div class="modal-steps-bar" id="modalStepsBar">
                            <div class="m-step active" id="mst1">
                                <div class="m-step-circle" id="msc1">1</div>
                                <div class="m-step-info">
                                    <div class="m-step-num">Step 1</div>
                                    <div class="m-step-name">Loan Details</div>
                                </div>
                            </div>
                            <div class="m-step pending" id="mst2">
                                <div class="m-step-circle" id="msc2">2</div>
                                <div class="m-step-info">
                                    <div class="m-step-num">Step 2</div>
                                    <div class="m-step-name">Breakdown</div>
                                </div>
                            </div>
                            <div class="m-step pending" id="mst3">
                                <div class="m-step-circle" id="msc3">3</div>
                                <div class="m-step-info">
                                    <div class="m-step-num">Step 3</div>
                                    <div class="m-step-name">Review & Submit</div>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 1 — Loan Details -->
                        <div class="modal-panel active" id="mp1">

                            @if(!$canApplyLoan)
                                <div class="sc-alert">
                                    <div class="sc-alert-ico">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10" />
                                            <line x1="12" y1="8" x2="12" y2="12" />
                                            <line x1="12" y1="16" x2="12.01" y2="16" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="sc-alert-title">Savings Requirement</div>
                                        <p class="sc-alert-text">
                                            @if($loanEligSettings->savings_to_loan_enabled)
                                                You need savings of at least the loan amount plus the
                                                ₱{{ number_format($loanEligSettings->savings_to_loan_ratio, 2) }} holdback to
                                                apply.
                                                You currently have <strong>₱{{ number_format($currentSavings, 2) }}</strong> in
                                                savings.
                                            @else
                                                Please contact the administrator to apply for a loan.
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <form id="loan-form" action="{{ route('lendingProgram') }}" method="post"
                                enctype="multipart/form-data" {{ !$canApplyLoan ? 'data-prevent-submit' : '' }}>
                                @csrf

                                <div class="panel-sec-hd">Loan Information</div>

                                <div class="p-form-row">
                                    <div class="p-field">
                                        <label>Loan Type <span class="req">*</span></label>
                                        <div class="p-sel-wrap">
                                            @php
                                                $mOptData = function ($loanType) use ($loanSettings) {
                                                    $s = $loanSettings[$loanType] ?? [];
                                                    return sprintf(
                                                        'data-rate="%s" data-processing="%s" data-service="%s" data-protection="%s" data-retention="%s"',
                                                        $s['interest_rate'] ?? 0.02,
                                                        $s['processing_fee_rate'] ?? 0,
                                                        $s['service_fee_rate'] ?? 0,
                                                        $s['loan_protection_fee'] ?? 0,
                                                        $s['retention_unpaid_rate'] ?? 0
                                                    );
                                                };
                                            @endphp
                                            <select class="p-select np" name="lending_type" id="lending_type"
                                                data-action="m-term-change" {{ !$canApplyLoan ? 'disabled' : '' }}
                                                required>
                                                <option value="">Select type</option>
                                                @foreach($loanSettings as $dbType => $s)
                                                    <option value="{{ $dbType }}" {!! $mOptData($dbType) !!}>{{ $dbType }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="p-field-error" id="err-lending_type">
                                            <i class="fa fa-circle-exclamation"></i> Loan Type is required.
                                        </div>
                                    </div>
                                    <div class="p-field">
                                        <label>
                                            Loan Amount (₱) <span class="req">*</span>
                                            <span class="max-pill">Max
                                                ₱{{ number_format($remainingLoanable, 0) }}</span>
                                        </label>
                                        <div class="p-inp-wrap">
                                            <span class="p-inp-ico" style="font-size:13px;font-weight:700;">₱</span>
                                            <input type="number" class="p-input" name="lending_amount" id="mLoanAmount"
                                                placeholder="e.g. 15000" min="1" max="{{ $remainingLoanable }}"
                                                data-action="m-amount-input" data-trigger="input" data-numeric-guard {{ !$canApplyLoan || $hasFullyLoaned ? 'disabled' : '' }} required>
                                        </div>
                                        <div class="p-warn" id="loan-limit-warning">
                                            <i class="fa fa-circle-exclamation" style="margin-right:5px;"></i>
                                            You can only borrow up to
                                            <strong>₱{{ number_format($remainingLoanable, 2) }}</strong> more.
                                        </div>
                                        <div class="p-field-error" id="err-mLoanAmount">
                                            <i class="fa fa-circle-exclamation"></i> Loan Amount is required.
                                        </div>
                                    </div>
                                </div>

                                <div class="p-form-row">
                                    <div class="p-field">
                                        <label>Loan Term <span class="req">*</span></label>
                                        <div class="p-sel-wrap">
                                            <select class="p-select np" name="lending_type_term_nonbusiness"
                                                id="lending_type_term_nonbusiness" data-action="m-term-sync" {{ !$canApplyLoan ? 'disabled' : '' }}>
                                                <option value="">Select term</option>
                                                <option value="6 months">6 months</option>
                                            </select>
                                        </div>
                                        <div style="display:none" id="term-business-wrap">
                                            <div class="p-sel-wrap">
                                                <select class="p-select np" name="lending_type_term_business"
                                                    id="lending_type_term_business" data-action="m-term-sync">
                                                    <option value="">Select term</option>
                                                    <option value="6 months">6 months</option>
                                                    <option value="12 months">12 months</option>
                                                </select>
                                            </div>
                                        </div>
                                        <input type="hidden" name="lending_type_term" id="lending_type_term">
                                        <div class="p-field-error" id="err-lending_type_term">
                                            <i class="fa fa-circle-exclamation"></i> Loan Term is required.
                                        </div>
                                    </div>
                                    <div class="p-field">
                                        <label>
                                            Monthly Income (₱) <span class="req">*</span>
                                            <span class="max-pill" id="income-locked-badge"
                                                style="display:none;">Saved</span>
                                        </label>
                                        <div class="p-inp-wrap">
                                            <span class="p-inp-ico"><svg viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2">
                                                    <rect x="2" y="7" width="20" height="14" rx="2" />
                                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                                                </svg></span>
                                            <input type="number" class="p-input" name="monthly_income"
                                                id="mMonthlyIncome" placeholder="Monthly income"
                                                data-action="m-income-input" data-trigger="input" data-numeric-guard {{ !$canApplyLoan ? 'disabled' : '' }} required>
                                        </div>
                                        <span class="p-hint" id="income-locked-hint" style="display:none;">
                                            This was saved from your previous application and can't be changed here.
                                        </span>
                                        <div class="p-field-error" id="err-mMonthlyIncome">
                                            <i class="fa fa-circle-exclamation"></i> Monthly Income is required.
                                        </div>
                                    </div>
                                </div>

                                <div class="panel-sec-hd">Purpose & Details</div>

                                <div class="p-form-row single">
                                    <div class="p-field">
                                        <label>Purpose of Loan <span class="req">*</span></label>
                                        <div class="p-sel-wrap">
                                            <select class="p-select np" name="purpose_loan" id="purpose_loan_select"
                                                data-action="m-purpose-change" {{ !$canApplyLoan ? 'disabled' : '' }}
                                                required>
                                                <option value="" disabled selected>Select purpose</option>
                                                <option value="Medical Expenses">Medical Expenses</option>
                                                <option value="Education">Education</option>
                                                <option value="Business Capital">Business Capital</option>
                                                <option value="Emergency Needs">Emergency Needs</option>
                                                <option value="Home Improvement">Home Improvement</option>
                                                <option value="Debt Consolidation">Debt Consolidation</option>
                                                <option value="Transportation">Transportation</option>
                                                <option value="Daily Expenses">Daily Expenses</option>
                                                <option value="Travel">Travel</option>
                                                <option value="Others">Others</option>
                                            </select>
                                        </div>
                                        <div class="p-field-error" id="err-purpose_loan_select">
                                            <i class="fa fa-circle-exclamation"></i> Purpose of Loan is required.
                                        </div>
                                    </div>
                                </div>

                                <div id="others-textarea-wrapper" style="display:none;margin-bottom:14px;">
                                    <div class="p-field">
                                        <label>Describe the Purpose <span class="req">*</span></label>
                                        <textarea class="p-textarea" name="purpose_loan_others"
                                            id="purpose_loan_textarea"
                                            placeholder="Describe the purpose of your loan..." data-action="mClearError"
                                            data-arg='["|el|"]' data-trigger="input" {{ !$canApplyLoan ? 'disabled' : '' }}></textarea>
                                        <span class="p-hint">Additional details help with faster approval.</span>
                                        <div class="p-field-error" id="err-purpose_loan_textarea">
                                            <i class="fa fa-circle-exclamation"></i> Please describe the purpose.
                                        </div>
                                    </div>
                                </div>

                                <!-- Net Proceeds Adjustment (Optional) -->
                                <div class="panel-sec-hd" style="margin-top:4px;">Net Proceeds Adjustment
                                    (Optional)</div>

                                <div class="p-form-row single">
                                    <div class="p-field">
                                        <label>Adjustment Type</label>
                                        <div class="p-sel-wrap">
                                            <select class="p-select np" name="net_proceeds_adjustment_type"
                                                id="mAdjustType" data-action="m-adjust-change" {{ !$canApplyLoan ? 'disabled' : '' }}>
                                                <option value="">No Adjustment</option>
                                                <option value="add">Add (+)</option>
                                                <option value="deduct">Deduct (−)</option>
                                            </select>
                                        </div>
                                        <span class="p-hint" id="mAdjustCaption">Charges are deducted — you receive
                                            less than your loan amount.</span>
                                    </div>
                                </div>

                                <!-- Supporting Documents -->
                                <div id="docs-wrapper-modal" style="display:none;">
                                    <div class="docs-heading">Supporting Documents</div>

                                    <!-- Personal -->
                                    <div id="docs-personal-modal" class="docs-section">
                                        <div class="upload-grid-modal">
                                            <div class="upload-card-modal" id="mcard-vid-personal">
                                                <div class="uc-icon"><svg viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                        <polyline points="17 8 12 3 7 8" />
                                                        <line x1="12" y1="3" x2="12" y2="15" />
                                                    </svg></div>
                                                <div class="uc-label">Valid ID</div>
                                                <div class="uc-sub">PDF, JPG or PNG · max 5MB</div>
                                                <span class="uc-badge required">Required</span>
                                                <div class="uc-filename" id="mname-vid-personal"></div>
                                                <input type="file" name="personal_valid_id"
                                                    accept=".jpg,.jpeg,.png,.pdf" data-action="mOnFileSelected"
                                                    data-arg='["|el|","mcard-vid-personal","mname-vid-personal"]'>
                                            </div>
                                            <div class="upload-card-modal" id="mcard-poi-personal">
                                                <div class="uc-icon"><svg viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                        <polyline points="17 8 12 3 7 8" />
                                                        <line x1="12" y1="3" x2="12" y2="15" />
                                                    </svg></div>
                                                <div class="uc-label">Proof of Income</div>
                                                <div class="uc-sub">Payslip / COE · PDF or image</div>
                                                <span class="uc-badge required">Required</span>
                                                <div class="uc-filename" id="mname-poi-personal"></div>
                                                <input type="file" name="personal_proof_of_income"
                                                    accept=".jpg,.jpeg,.png,.pdf" data-action="mOnFileSelected"
                                                    data-arg='["|el|","mcard-poi-personal","mname-poi-personal"]'>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Emergency -->
                                    <div id="docs-emergency-modal" class="docs-section">
                                        <div class="upload-grid-modal">
                                            <div class="upload-card-modal" id="mcard-vid-emergency">
                                                <div class="uc-icon"><svg viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                        <polyline points="17 8 12 3 7 8" />
                                                        <line x1="12" y1="3" x2="12" y2="15" />
                                                    </svg></div>
                                                <div class="uc-label">Valid ID</div>
                                                <div class="uc-sub">PDF, JPG or PNG · max 5MB</div>
                                                <span class="uc-badge required">Required</span>
                                                <div class="uc-filename" id="mname-vid-emergency"></div>
                                                <input type="file" name="emergency_valid_id"
                                                    accept=".jpg,.jpeg,.png,.pdf" data-action="mOnFileSelected"
                                                    data-arg='["|el|","mcard-vid-emergency","mname-vid-emergency"]'>
                                            </div>
                                            <div class="upload-card-modal" id="mcard-poi-emergency">
                                                <div class="uc-icon"><svg viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                        <polyline points="17 8 12 3 7 8" />
                                                        <line x1="12" y1="3" x2="12" y2="15" />
                                                    </svg></div>
                                                <div class="uc-label">Proof of Income</div>
                                                <div class="uc-sub">PDF, JPG or PNG · max 5MB</div>
                                                <span class="uc-badge required">Required</span>
                                                <div class="uc-filename" id="mname-poi-emergency"></div>
                                                <input type="file" name="emergency_proof_of_income"
                                                    accept=".jpg,.jpeg,.png,.pdf" data-action="mOnFileSelected"
                                                    data-arg='["|el|","mcard-poi-emergency","mname-poi-emergency"]'>
                                            </div>
                                            <div class="upload-card-modal" id="mcard-poe-emergency">
                                                <div class="uc-icon"><svg viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                        <polyline points="17 8 12 3 7 8" />
                                                        <line x1="12" y1="3" x2="12" y2="15" />
                                                    </svg></div>
                                                <div class="uc-label">Proof of Emergency</div>
                                                <div class="uc-sub">Medical Cert, Hospital Bill, Police Report</div>
                                                <span class="uc-badge required">Required</span>
                                                <div class="uc-filename" id="mname-poe-emergency"></div>
                                                <input type="file" name="proof_of_emergency"
                                                    accept=".jpg,.jpeg,.png,.pdf" data-action="mOnFileSelected"
                                                    data-arg='["|el|","mcard-poe-emergency","mname-poe-emergency"]'>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Business -->
                                    <div id="docs-business-modal" class="docs-section">
                                        <div class="upload-grid-modal">
                                            <div class="upload-card-modal" id="mcard-vid-business">
                                                <div class="uc-icon"><svg viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                        <polyline points="17 8 12 3 7 8" />
                                                        <line x1="12" y1="3" x2="12" y2="15" />
                                                    </svg></div>
                                                <div class="uc-label">Valid ID</div>
                                                <div class="uc-sub">PDF, JPG or PNG · max 5MB</div>
                                                <span class="uc-badge required">Required</span>
                                                <div class="uc-filename" id="mname-vid-business"></div>
                                                <input type="file" name="business_valid_id"
                                                    accept=".jpg,.jpeg,.png,.pdf" data-action="mOnFileSelected"
                                                    data-arg='["|el|","mcard-vid-business","mname-vid-business"]'>
                                            </div>
                                            <div class="upload-card-modal" id="mcard-poi-business">
                                                <div class="uc-icon"><svg viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                        <polyline points="17 8 12 3 7 8" />
                                                        <line x1="12" y1="3" x2="12" y2="15" />
                                                    </svg></div>
                                                <div class="uc-label">Proof of Income</div>
                                                <div class="uc-sub">PDF, JPG or PNG · max 5MB</div>
                                                <span class="uc-badge required">Required</span>
                                                <div class="uc-filename" id="mname-poi-business"></div>
                                                <input type="file" name="business_proof_of_income"
                                                    accept=".jpg,.jpeg,.png,.pdf" data-action="mOnFileSelected"
                                                    data-arg='["|el|","mcard-poi-business","mname-poi-business"]'>
                                            </div>
                                            <div class="upload-card-modal" id="mcard-bp-business">
                                                <div class="uc-icon"><svg viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                        <polyline points="17 8 12 3 7 8" />
                                                        <line x1="12" y1="3" x2="12" y2="15" />
                                                    </svg></div>
                                                <div class="uc-label">Business Permit / DTI</div>
                                                <div class="uc-sub">PDF, JPG or PNG · max 5MB</div>
                                                <span class="uc-badge required">Required</span>
                                                <div class="uc-filename" id="mname-bp-business"></div>
                                                <input type="file" name="business_permit" accept=".jpg,.jpeg,.png,.pdf"
                                                    data-action="mOnFileSelected"
                                                    data-arg='["|el|","mcard-bp-business","mname-bp-business"]'>
                                            </div>
                                            <div class="upload-card-modal" id="mcard-fs-business">
                                                <div class="uc-icon"><svg viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                        <polyline points="17 8 12 3 7 8" />
                                                        <line x1="12" y1="3" x2="12" y2="15" />
                                                    </svg></div>
                                                <div class="uc-label">Financial Statement</div>
                                                <div class="uc-sub">PDF, JPG or PNG · max 5MB</div>
                                                <span class="uc-badge optional">Optional</span>
                                                <div class="uc-filename" id="mname-fs-business"></div>
                                                <input type="file" name="financial_statement"
                                                    accept=".jpg,.jpeg,.png,.pdf" data-action="mOnFileSelected"
                                                    data-arg='["|el|","mcard-fs-business","mname-fs-business"]'>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Education -->
                                    <div id="docs-education-modal" class="docs-section">
                                        <div class="upload-grid-modal">
                                            <div class="upload-card-modal" id="mcard-sid-education">
                                                <div class="uc-icon"><svg viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                        <polyline points="17 8 12 3 7 8" />
                                                        <line x1="12" y1="3" x2="12" y2="15" />
                                                    </svg></div>
                                                <div class="uc-label">School ID</div>
                                                <div class="uc-sub">PDF, JPG or PNG · max 5MB</div>
                                                <span class="uc-badge required">Required</span>
                                                <div class="uc-filename" id="mname-sid-education"></div>
                                                <input type="file" name="school_id" accept=".jpg,.jpeg,.png,.pdf"
                                                    data-action="mOnFileSelected"
                                                    data-arg='["|el|","mcard-sid-education","mname-sid-education"]'>
                                            </div>
                                            <div class="upload-card-modal" id="mcard-cor-education">
                                                <div class="uc-icon"><svg viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                        <polyline points="17 8 12 3 7 8" />
                                                        <line x1="12" y1="3" x2="12" y2="15" />
                                                    </svg></div>
                                                <div class="uc-label">COR (Certificate of Registration)</div>
                                                <div class="uc-sub">PDF, JPG or PNG · max 5MB</div>
                                                <span class="uc-badge required">Required</span>
                                                <div class="uc-filename" id="mname-cor-education"></div>
                                                <input type="file" name="cor" accept=".jpg,.jpeg,.png,.pdf"
                                                    data-action="mOnFileSelected"
                                                    data-arg='["|el|","mcard-cor-education","mname-cor-education"]'>
                                            </div>
                                            <div class="upload-card-modal" id="mcard-vid-education">
                                                <div class="uc-icon"><svg viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                        <polyline points="17 8 12 3 7 8" />
                                                        <line x1="12" y1="3" x2="12" y2="15" />
                                                    </svg></div>
                                                <div class="uc-label">Valid ID</div>
                                                <div class="uc-sub">PDF, JPG or PNG · max 5MB</div>
                                                <span class="uc-badge required">Required</span>
                                                <div class="uc-filename" id="mname-vid-education"></div>
                                                <input type="file" name="education_valid_id"
                                                    accept=".jpg,.jpeg,.png,.pdf" data-action="mOnFileSelected"
                                                    data-arg='["|el|","mcard-vid-education","mname-vid-education"]'>
                                            </div>
                                        </div>
                                    </div>

                                    @if($hasFullyLoaned)
                                        <div class="loan-status-banner lsb-danger" style="margin-top:14px;">
                                            <div class="lsb-ico danger-ico"><i class="fa fa-ban"
                                                    style="color:#dc2626;font-size:15px;"></i></div>
                                            <div>
                                                <div class="lsb-title">Loan Limit Reached</div>
                                                <p style="margin:0;font-size:12.5px;line-height:1.5;">You have an active
                                                    loan of <strong>₱{{ number_format($totalActiveLoan, 2) }}</strong> — the
                                                    max is <strong>₱{{ number_format($effectiveCeiling, 2) }}</strong>.
                                                    Please repay before applying again.
                                                </p>
                                            </div>
                                        </div>
                                    @elseif($totalActiveLoan > 0)
                                        <div class="loan-status-banner lsb-warn" style="margin-top:14px;">
                                            <div class="lsb-ico warn-ico"><i class="fa fa-circle-info"
                                                    style="color:#e6a817;font-size:15px;"></i></div>
                                            <div style="width:100%;">
                                                <div class="lsb-title">Remaining Loanable Amount</div>
                                                <p style="margin:0 0 8px;font-size:12.5px;line-height:1.5;">You have an
                                                    active loan of
                                                    <strong>₱{{ number_format($totalActiveLoan, 2) }}</strong>. You may
                                                    still borrow up to:
                                                </p>
                                                <div
                                                    style="background:#f5f5f5;border-radius:20px;height:8px;overflow:hidden;margin-bottom:6px;">
                                                    <div
                                                        style="height:8px;border-radius:20px;background:linear-gradient(90deg,#e6a817,#f59e0b);width:{{ min(100, ($totalActiveLoan / max(1, $effectiveCeiling)) * 100) }}%;">
                                                    </div>
                                                </div>
                                                <div
                                                    style="display:flex;justify-content:space-between;font-size:11px;color:#999;margin-bottom:8px;">
                                                    <span>Used: ₱{{ number_format($totalActiveLoan, 2) }}</span>
                                                    <span>Up to: ₱{{ number_format($effectiveCeiling, 2) }}</span>
                                                </div>
                                                <div
                                                    style="background:#fff;border:1.5px solid #ffe082;border-radius:8px;padding:7px 12px;display:inline-flex;align-items:center;gap:7px;font-size:13px;font-weight:700;color: var(--teal);">
                                                    <i class="fa fa-coins"></i>
                                                    Available: ₱{{ number_format($remainingLoanable, 2) }}
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="loan-status-banner lsb-ok" style="margin-top:14px;">
                                            <i class="fa fa-circle-info" style="margin-top:2px;flex-shrink:0;"></i>
                                            <span>You may borrow up to
                                                <strong>₱{{ number_format($effectiveCeiling, 2) }}</strong>. Applications
                                                exceeding
                                                this limit will not be processed.</span>
                                        </div>
                                    @endif
                                </div>

                                <input type="hidden" name="monthly_payment" id="hidden-monthly">
                                <input type="hidden" name="total_payment" id="hidden-total">
                                <input type="hidden" name="total_interest" id="hidden-interest">

                            </form>

                            <div class="modal-footer-bar">
                                <div class="mf-note">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                    </svg>
                                    Your data is encrypted
                                </div>
                                <div class="mf-btns">
                                    <button class="m-btn m-btn-outline" data-action="closeLoanModal">Cancel</button>
                                    <button class="m-btn m-btn-primary" data-action="mGoStep2" {{ !$canApplyLoan ? 'disabled' : '' }}>
                                        View Breakdown
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <polyline points="9 18 15 12 9 6" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- /Panel 1 -->

                        <!-- PANEL 2 — Charges & Breakdown -->
                        <div class="modal-panel" id="mp2">
                            <div class="panel-sec-hd">Charges & Breakdown</div>
                            <div class="breakdown-grid-modal">
                                <div class="b-box-m hl">
                                    <div class="b-ico-m bi-g"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <line x1="12" y1="1" x2="12" y2="23" />
                                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                        </svg></div>
                                    <div class="b-lbl-m">Principal</div>
                                    <div class="b-val-m" id="cb-pri">₱ —</div>
                                    <div class="b-hint-m">Amount you're borrowing</div>
                                </div>
                                <div class="b-box-m">
                                    <div class="b-ico-m bi-o"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <circle cx="12" cy="12" r="10" />
                                            <polyline points="12 6 12 12 16 14" />
                                        </svg></div>
                                    <div class="b-lbl-m">Interest Rate</div>
                                    <div class="b-val-m" id="cb-rate">— %</div>
                                    <div class="b-hint-m">Monthly, diminishing balance</div>
                                </div>
                                <div class="b-box-m">
                                    <div class="b-ico-m bi-r"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                        </svg></div>
                                    <div class="b-lbl-m">Total Interest</div>
                                    <div class="b-val-m" id="cb-int">₱ —</div>
                                    <div class="b-hint-m">Cost of borrowing</div>
                                </div>
                                <div class="b-box-m">
                                    <div class="b-ico-m bi-b"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <rect x="3" y="4" width="18" height="18" rx="2" />
                                            <line x1="16" y1="2" x2="16" y2="6" />
                                            <line x1="8" y1="2" x2="8" y2="6" />
                                            <line x1="3" y1="10" x2="21" y2="10" />
                                        </svg></div>
                                    <div class="b-lbl-m">Term</div>
                                    <div class="b-val-m" id="cb-term">— mo</div>
                                    <div class="b-hint-m">Repayment period</div>
                                </div>
                                <div class="b-box-m">
                                    <div class="b-ico-m bi-p"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <circle cx="12" cy="12" r="10" />
                                            <line x1="12" y1="8" x2="12" y2="12" />
                                            <line x1="12" y1="16" x2="12.01" y2="16" />
                                        </svg></div>
                                    <div class="b-lbl-m">Processing Fee</div>
                                    <div class="b-val-m" id="cb-proc">₱ —</div>
                                    <div class="b-hint-m" id="cb-proc-hint">Processing & collection</div>
                                </div>
                                <div class="b-box-m">
                                    <div class="b-ico-m bi-t"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                        </svg></div>
                                    <div class="b-lbl-m">Service & Legal Fee</div>
                                    <div class="b-val-m" id="cb-svc">₱ —</div>
                                    <div class="b-hint-m" id="cb-svc-hint">One-time fee</div>
                                </div>
                                <div class="b-box-m">
                                    <div class="b-ico-m bi-o"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <path
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg></div>
                                    <div class="b-lbl-m">Loan Protection Plan</div>
                                    <div class="b-val-m" id="cb-protect">₱ —</div>
                                    <div class="b-hint-m" id="cb-protect-hint">Per month of term</div>
                                </div>
                                <div class="b-box-m">
                                    <div class="b-ico-m bi-r"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <rect x="3" y="11" width="18" height="10" rx="2" />
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                        </svg></div>
                                    <div class="b-lbl-m">Retention / CBU</div>
                                    <div class="b-val-m" id="cb-retention">₱ —</div>
                                    <div class="b-hint-m" id="cb-retention-hint">Held as capital build-up</div>
                                </div>
                                <div class="b-box-m" id="cb-adjust-box" style="display:none;">
                                    <div class="b-ico-m bi-s"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <path d="M12 5v14M5 12h14" />
                                            <path d="M20 12V8H6a2 2 0 0 1 0-4h12v4" />
                                        </svg></div>
                                    <div class="b-lbl-m">Net Proceeds Adjustment</div>
                                    <div class="b-val-m" id="cb-adjust">₱ 0.00</div>
                                    <div class="b-hint-m" id="cb-adjust-hint">Charges added back — full loan released
                                    </div>
                                </div>
                                <div class="b-box-m hl">
                                    <div class="b-ico-m bi-n"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <path d="M20 12V8H6a2 2 0 0 1 0-4h12v4" />
                                            <path d="M4 6v12a2 2 0 0 0 2 2h14v-4" />
                                            <path d="M18 12a2 2 0 0 0 0 4h4v-4Z" />
                                        </svg></div>
                                    <div class="b-lbl-m">Net Proceeds</div>
                                    <div class="b-val-m" id="cb-net">₱ —</div>
                                    <div class="b-hint-m">Amount released to you</div>
                                </div>
                                <div class="b-box-m">
                                    <div class="b-ico-m bi-r"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                        </svg></div>
                                    <div class="b-lbl-m">Total Charges</div>
                                    <div class="b-val-m" id="cb-charges">₱ —</div>
                                    <div class="b-hint-m">Interest + all fees</div>
                                </div>
                                <div class="b-box-m hl">
                                    <div class="b-ico-m bi-t"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                            <polyline points="22 4 12 14.01 9 11.01" />
                                        </svg></div>
                                    <div class="b-lbl-m">Total Payable</div>
                                    <div class="b-val-m" id="cb-total">₱ —</div>
                                    <div class="b-hint-m">Principal + Interest</div>
                                </div>
                            </div>

                            <div class="amort-hd-m">Monthly Amortization Schedule</div>
                            <div class="amort-wrap-m">
                                <div class="amort-scroll-m">
                                    <table class="amort-tbl">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th>Payment</th>
                                                <th>Principal</th>
                                                <th>Interest</th>
                                                <th>Remaining</th>
                                            </tr>
                                        </thead>
                                        <tbody id="amortBody">
                                            <tr>
                                                <td colspan="5"
                                                    style="text-align:center;color:var(--muted);padding:16px">No data
                                                    yet.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="modal-footer-bar">
                                <div class="mf-note">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <line x1="12" y1="8" x2="12" y2="12" />
                                        <line x1="12" y1="16" x2="12.01" y2="16" />
                                    </svg>
                                    Rates are indicative
                                </div>
                                <div class="mf-btns">
                                    <button class="m-btn m-btn-outline" data-action="mGoStep" data-arg='[1,true]'>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <polyline points="15 18 9 12 15 6" />
                                        </svg>
                                        Back
                                    </button>
                                    <button class="m-btn m-btn-primary" data-action="mGoStep3">
                                        Proceed to Review
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <polyline points="9 18 15 12 9 6" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- /Panel 2 -->

                        <!-- PANEL 3 — Review & Submit -->
                        <div class="modal-panel" id="mp3">
                            <div class="confirm-hero-m">
                                <div class="confirm-ring-m">
                                    <i class="fa fa-shield"></i>
                                </div>
                                <div class="confirm-title-m">Review Your Application</div>
                                <div class="confirm-sub-m">Verify all details carefully before submitting.</div>
                            </div>

                            <div class="sum-card-m">
                                <div class="sum-head-m">Application Summary</div>
                                <div class="sum-row-m"><span class="sum-lbl-m">Loan Type</span><span
                                        class="sum-val-m green" id="cf-type">—</span></div>
                                <div class="sum-row-m"><span class="sum-lbl-m">Amount Requested</span><span
                                        class="sum-val-m" id="cf-amount">—</span></div>
                                <div class="sum-row-m"><span class="sum-lbl-m">Loan Term</span><span class="sum-val-m"
                                        id="cf-term">—</span></div>
                                <div class="sum-row-m"><span class="sum-lbl-m">Monthly Income</span><span
                                        class="sum-val-m" id="cf-income">—</span></div>
                                <div class="sum-row-m"><span class="sum-lbl-m">Interest Rate</span><span
                                        class="sum-val-m" id="cf-rate">—</span></div>
                                <div class="sum-row-m"><span class="sum-lbl-m">Purpose</span><span class="sum-val-m"
                                        id="cf-purpose">—</span></div>
                            </div>

                            {{-- CARD 1: Fees & Charges --}}
                            <div class="sum-card-m">
                                <div class="sum-head-m">Fees & Charges</div>

                                <div class="sum-row-m adjust" id="cf-adjust-row" style="display:none;">
                                    <span class="sum-lbl-m">Net Proceeds Adjustment</span>
                                    <span class="sum-val-m green" id="cf-adjust">—</span>
                                </div>
                                <div class="sum-row-m subtle"><span class="sum-lbl-m">Processing Fee</span><span
                                        class="sum-val-m deduct" id="cf-processing">—</span></div>
                                <div class="sum-row-m subtle"><span class="sum-lbl-m">Service & Legal Fee</span><span
                                        class="sum-val-m deduct" id="cf-service">—</span></div>
                                <div class="sum-row-m subtle"><span class="sum-lbl-m">Loan Protection Plan</span><span
                                        class="sum-val-m deduct" id="cf-protection">—</span></div>
                                <div class="sum-row-m subtle"><span class="sum-lbl-m">Retention / CBU</span><span
                                        class="sum-val-m deduct" id="cf-retention">—</span></div>
                            </div>

                            {{-- CARD 2: Repayment Summary --}}
                            <div class="sum-card-m">
                                <div class="sum-head-m">Repayment Summary</div>
                                <div class="sum-row-m"><span class="sum-lbl-m">Total Interest</span><span
                                        class="sum-val-m" id="cf-int">—</span></div>
                                <div class="sum-row-m"><span class="sum-lbl-m">Average Monthly Payment</span><span
                                        class="sum-val-m gold" id="cf-monthly">—</span></div>
                                <div class="sum-row-m"><span class="sum-lbl-m">Total Charges</span><span
                                        class="sum-val-m" id="cf-charges">—</span></div>
                                <div class="sum-row-m total"><span class="sum-lbl-m bold">Total Payable</span><span
                                        class="sum-val-m green bigf" id="cf-total">—</span></div>
                            </div>

                            {{-- CARD 3: Total Proceeds --}}
                            <div class="sum-card-m">
                                <div class="sum-head-m">Total Proceeds</div>

                                <div class="sum-row-m"><span class="sum-lbl-m">Total Loan Amount</span><span
                                        class="sum-val-m" id="cf-tot-amount">—</span></div>
                                <div class="sum-row-m"><span class="sum-lbl-m">Total Fees</span><span
                                        class="sum-val-m deduct" id="cf-totfees">—</span></div>
                                <div class="sum-row-m adjust" id="cf-tot-adjust-row" style="display:none;"><span
                                        class="sum-lbl-m">Net Proceeds Adjustment</span><span class="sum-val-m green"
                                        id="cf-tot-adjust">—</span></div>
                                <div class="sum-row-m total"><span class="sum-lbl-m bold">Net Proceeds (you
                                        receive)</span><span class="sum-val-m green bigf" id="cf-net">—</span></div>
                            </div>

                            <div class="cb-row-m" id="agreeRow" data-action="m-agree-row-click">
                                <input type="checkbox" id="mAgree" data-action="m-agree-check" data-stop>
                                <label for="mAgree" data-action="stop-propagation">I confirm all information is
                                    accurate and I agree to the <strong>Terms and Conditions</strong> of KPMPCATS
                                    Cooperative.</label>
                            </div>
                            <div class="cb-required-msg" id="err-mAgree">
                                <i class="fa fa-circle-exclamation"></i> You must agree to the Terms and Conditions.
                            </div>

                            <div class="modal-footer-bar">
                                <div class="mf-note">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                    </svg>
                                    Data is encrypted
                                </div>
                                <div class="mf-btns">
                                    <button class="m-btn m-btn-outline" data-action="mGoStep" data-arg='[2,true]'>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <polyline points="15 18 9 12 15 6" />
                                        </svg>
                                        Back
                                    </button>
                                    <button class="m-btn m-btn-gold" data-action="mSubmit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <polyline points="22 2 11 13" />
                                            <polygon points="22 2 15 22 11 13 2 9 22 2" />
                                        </svg>
                                        Submit Application
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- /Panel 3 -->

                        <!-- SUCCESS SCREEN -->
                        <div class="success-screen-m" id="mSuccess">
                            <div class="success-ring-m"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg></div>
                            <div class="success-title-m">Application Submitted!</div>
                            <p class="success-sub-m">Your application is now under review. You will be notified through
                                your member portal once a decision is made.</p>
                            <div class="ref-pill-m">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Ref: <span id="mRefDisplay"></span>
                            </div>
                            <br>
                            <button class="m-btn m-btn-primary" style="margin:0 auto;"
                                data-action="m-dashboard-back">Back to Dashboard</button>
                        </div>

                    </div><!-- /modal-main-area -->
                </div><!-- /modal-layout -->
            </div><!-- /loan-modal -->
        </div><!-- /loan-modal-overlay -->

        @if(session("ApplySuccess"))
            <div class="modal-overlay-success" id="success-modal">
                <div class="success-modal-box">
                    <div class="sm-head">
                        <div class="sm-icon">
                            <i class="fa fa-check"></i>
                        </div>
                    </div>
                    <div class="sm-body">
                        <h2>Application Submitted!</h2>
                        <p>Your lending application has been received and is now under review. We'll notify you within 3–5
                            business days.</p>
                        <div class="sm-details">
                            <div class="sm-row"><span class="sm-label">Name</span><span
                                    class="sm-val">{{ session("MemberName") }}</span></div>
                            <div class="sm-row"><span class="sm-label">Status</span><span class="sm-badge">Pending
                                    Review</span></div>
                            <div class="sm-row"><span class="sm-label">Reference</span><span
                                    class="sm-val">#{{ session("ReferenceNo") }}</span></div>
                            <div class="sm-row"><span class="sm-label">Date Filed</span><span
                                    class="sm-val">{{ session("DateFiled") }}</span></div>
                        </div>
                        <button class="sm-btn" data-action="closeSuccessModal">Got it, Continue</button>
                    </div>
                </div>
            </div>
        @endif

    </div><!-- /container-fluid -->

    <script nonce="{{ csp_nonce() }}">
        function applyFilters(listId, searchId, dateId, statusId) {
            const list = document.getElementById(listId);
            if (!list) return;

            const searchVal = (document.getElementById(searchId)?.value || '').toLowerCase().trim();
            const dateVal = document.getElementById(dateId)?.value || ''; // '' = All Dates, else 'YYYY-MM-DD'
            const statusVal = document.getElementById(statusId)?.value || 'all';

            list.querySelectorAll('.loan-card').forEach(card => {
                let visible = true;

                if (searchVal && !card.textContent.toLowerCase().includes(searchVal)) {
                    visible = false;
                }

                if (visible && statusVal !== 'all' && card.dataset.status !== statusVal) {
                    visible = false;
                }

                if (visible && dateVal && card.dataset.date !== dateVal) {
                    visible = false;
                }

                card.dataset.filteredOut = visible ? 'false' : 'true';
            });

            pgState[listId] = 1;
            paginateList(listId);
        }
    </script>

    <script nonce="{{ csp_nonce() }}">
        // ══════════════════════════════════════════════════════════
        //  MODAL OPEN / CLOSE
        // ══════════════════════════════════════════════════════════
        function openLoanModal() {
            document.getElementById('loanModalOverlay').classList.add('open');
            document.body.style.overflow = 'hidden';
            mReset();
        }
        function closeLoanModal() {
            document.getElementById('loanModalOverlay').classList.remove('open');
            document.body.style.overflow = '';
        }
        function maybeCloseLoanModal(e) {
            if (e.target === document.getElementById('loanModalOverlay')) closeLoanModal();
        }

        // ══════════════════════════════════════════════════════════
        //  HELPERS
        // ══════════════════════════════════════════════════════════
        const MAX_REMAINING = {{ $remainingLoanable }};
        const SAVED_MONTHLY_INCOME = {{ $savedMonthlyIncome ?? 'null' }};
        const CURRENT_SAVINGS = {{ $currentSavings }};
        const SAVINGS_TO_LOAN_ENABLED = {{ $loanEligSettings->savings_to_loan_enabled ? 'true' : 'false' }};
        const SAVINGS_HOLD_BACK = {{ $loanEligSettings->savings_to_loan_ratio }};
        const fmt = n => '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // Read all fee settings from the selected <option data-*> attributes.
        // Mirrors what lendingController@lendingProgram actually charges server-side.
        function getSelectedFees() {
            const typeEl = document.getElementById('lending_type');
            const opt = typeEl.options[typeEl.selectedIndex];
            if (!opt || !opt.value) return null;
            const rate = parseFloat(opt.getAttribute('data-rate'));
            return {
                rate: (!isNaN(rate) && rate > 0) ? rate : 0.02,               // monthly interest, e.g. 0.02 = 2%
                processingRate: parseFloat(opt.getAttribute('data-processing')) || 0,   // %, e.g. 2
                serviceRate: parseFloat(opt.getAttribute('data-service')) || 0,         // %, e.g. 2
                protectionPerMonth: parseFloat(opt.getAttribute('data-protection')) || 0, // ₱ per month
                retentionRate: parseFloat(opt.getAttribute('data-retention')) || 0,     // %, e.g. 6
            };
        }

        // Full charge breakdown for a given principal/term — mirrors backend math exactly:
        //  - interest: diminishing balance (recomputed monthly on the remaining principal)
        //  - processing / service fees: % of principal, one-time
        //  - loan protection: flat ₱ amount per month of term
        //  - retention/CBU: % of principal, withheld
        //  - net proceeds: base unless Add (+), then the charges are added back
        //    so the member receives the full loan amount
        function mCalcBreakdown(principal, fees, termMonths, isAdd) {
            const r = fees.rate;
            const basePrincipal = termMonths > 0 ? Math.round((principal / termMonths) * 100) / 100 : 0;
            const lastPrincipal = termMonths > 0 ? Math.round((principal - basePrincipal * (termMonths - 1)) * 100) / 100 : 0;

            let totalInterest = 0;
            let balance = principal;
            for (let i = 1; i <= termMonths; i++) {
                const principalDue = i === termMonths ? lastPrincipal : basePrincipal;
                const interestDue = Math.round(balance * r * 100) / 100;
                totalInterest += interestDue;
                balance = Math.round((balance - principalDue) * 100) / 100;
            }
            totalInterest = Math.round(totalInterest * 100) / 100;

            const processingFee = Math.round(principal * (fees.processingRate / 100) * 100) / 100;
            const serviceFee = Math.round(principal * (fees.serviceRate / 100) * 100) / 100;
            const protectionFee = Math.round(fees.protectionPerMonth * termMonths * 100) / 100;
            const retentionFee = Math.round(principal * (fees.retentionRate / 100) * 100) / 100;
            const baseNetProceeds = Math.round((principal - processingFee - serviceFee - protectionFee - retentionFee) * 100) / 100;
            const netProceeds = isAdd ? Math.round(principal * 100) / 100 : baseNetProceeds;
            const adjDelta = isAdd ? Math.round((netProceeds - baseNetProceeds) * 100) / 100 : 0;

            const totalFees = Math.round((processingFee + serviceFee + protectionFee + retentionFee) * 100) / 100;
            const totalPayment = Math.round((principal + totalInterest + totalFees) * 100) / 100;
            const monthlyPayment = termMonths > 0 ? Math.round((totalPayment / termMonths) * 100) / 100 : 0;

            return {
                basePrincipal, totalInterest, processingFee, serviceFee,
                protectionFee, retentionFee, baseNetProceeds,
                netProceeds, adjDelta, totalFees, totalPayment, monthlyPayment
            };
        }

        // ── Net Proceeds Adjustment helpers ───────────────────────
        function mIsAddSelected() {
            const el = document.getElementById('mAdjustType');
            return el ? el.value === 'add' : false;
        }

        function mUpdateAdjustCaption() {
            const capEl = document.getElementById('mAdjustCaption');
            if (!capEl) return;
            if (mIsAddSelected()) {
                capEl.textContent = 'Charges added back — you receive the full loan amount.';
            } else {
                capEl.textContent = 'Charges are deducted — you receive less than your loan amount.';
            }
        }

        function mHandleAdjustType(sel) {
            mUpdateAdjustCaption();
        }

        // ── Inline validation helpers ──────────────────────────────
        // Show error on a field (input/select/textarea)
        function mShowError(fieldEl, errId) {
            fieldEl.classList.add('field-error');
            const e = document.getElementById(errId);
            if (e) e.classList.add('show');
        }

        // Clear error from a field when user starts fixing it
        function mClearError(fieldEl) {
            fieldEl.classList.remove('field-error');
            const errId = 'err-' + fieldEl.id;
            const e = document.getElementById(errId);
            if (e) e.classList.remove('show');
        }

        // Scroll to and focus the first invalid field
        function mFocusFirst(el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            try { el.focus(); } catch (e) { }
        }

        // Agree checkbox error helpers
        function mClearAgreeError() {
            document.getElementById('agreeRow').classList.remove('cb-error');
            document.getElementById('err-mAgree').classList.remove('show');
        }

        // ══════════════════════════════════════════════════════════
        //  RESET
        // ══════════════════════════════════════════════════════════
        function mReset() {
            document.getElementById('lending_type').value = '';
            document.getElementById('mLoanAmount').value = '';
            const incomeEl = document.getElementById('mMonthlyIncome');
            const incomeHint = document.getElementById('income-locked-hint');
            const incomeBadge = document.getElementById('income-locked-badge');
            document.getElementById('purpose_loan_select').value = '';
            document.getElementById('purpose_loan_textarea').value = '';
            document.getElementById('lending_type_term_nonbusiness').value = '';
            document.getElementById('lending_type_term_business').value = '';
            document.getElementById('lending_type_term').value = '';
            const adjTypeEl = document.getElementById('mAdjustType');
            if (adjTypeEl) adjTypeEl.value = '';
            mUpdateAdjustCaption();
            document.getElementById('mAgree').checked = false;
            document.getElementById('mSuccess').classList.remove('show');
            document.getElementById('modalStepsBar').style.display = 'flex';

            // Clear all inline errors
            document.querySelectorAll('.field-error').forEach(el => el.classList.remove('field-error'));
            document.querySelectorAll('.p-field-error.show, .cb-required-msg.show').forEach(el => el.classList.remove('show'));
            document.getElementById('agreeRow').classList.remove('cb-error');

            [1, 2, 3].forEach(i => {
                const p = document.getElementById('mp' + i);
                p.classList.remove('active', 'back');
            });
            const adjBox = document.getElementById('cb-adjust-box');
            if (adjBox) adjBox.style.display = 'none';
            const cfAdjRow = document.getElementById('cf-adjust-row');
            if (cfAdjRow) cfAdjRow.style.display = 'none';
            document.getElementById('mp1').classList.add('active');
            mUpdateSteps(1);
            mCompute();
            ['personal', 'emergency', 'business', 'education'].forEach(t => {
                document.getElementById('docs-' + t + '-modal').style.display = 'none';
            });
            document.getElementById('docs-wrapper-modal').style.display = 'none';
            document.getElementById('others-textarea-wrapper').style.display = 'none';
            document.getElementById('lending_type_term_nonbusiness').style.display = 'block';
            document.getElementById('lending_type_term_business').style.display = 'none';

            if (SAVED_MONTHLY_INCOME) {
                incomeEl.value = SAVED_MONTHLY_INCOME;
                incomeEl.setAttribute('readonly', 'readonly');
                incomeEl.classList.add('locked-field');
                incomeHint.style.display = 'block';
                incomeBadge.style.display = 'inline-flex';
            } else {
                incomeEl.value = '';
                incomeEl.removeAttribute('readonly');
                incomeEl.classList.remove('locked-field');
                incomeHint.style.display = 'none';
                incomeBadge.style.display = 'none';
            }
        }

        // ══════════════════════════════════════════════════════════
        //  STEP MANAGEMENT
        // ══════════════════════════════════════════════════════════
        function mUpdateSteps(n) {
            [1, 2, 3].forEach(i => {
                const el = document.getElementById('mst' + i);
                const c = document.getElementById('msc' + i);
                el.className = 'm-step';
                if (i < n) { el.classList.add('done'); c.innerHTML = '✓'; }
                else if (i === n) { el.classList.add('active'); c.textContent = i; }
                else { el.classList.add('pending'); c.textContent = i; }
                const con = document.getElementById('mcon' + i);
                if (con) i < n ? con.classList.add('done') : con.classList.remove('done');
            });
        }
        function mGoStep(n, back = false) {
            [1, 2, 3].forEach(i => document.getElementById('mp' + i).classList.remove('active', 'back'));
            const p = document.getElementById('mp' + n);
            p.classList.add('active');
            if (back) p.classList.add('back');
            mUpdateSteps(n);
            document.querySelector('.modal-main-area').scrollTop = 0;
        }

        // ══════════════════════════════════════════════════════════
        //  TERM SELECTOR
        // ══════════════════════════════════════════════════════════
        function mUpdateTermOptions() {
            const type = document.getElementById('lending_type').value;
            const nbWrap = document.getElementById('lending_type_term_nonbusiness').closest('.p-sel-wrap')
                || document.getElementById('lending_type_term_nonbusiness').parentElement;
            const bWrap = document.getElementById('term-business-wrap');
            const nbEl = document.getElementById('lending_type_term_nonbusiness');
            const bEl = document.getElementById('lending_type_term_business');

            if (type === 'Business Loan') {
                nbWrap.style.display = 'none';
                bWrap.style.display = 'block';
                bEl.style.display = 'block';
                if (bEl.options.length > 1) bEl.selectedIndex = 1;
            } else if (type) {
                nbWrap.style.display = 'block';
                bWrap.style.display = 'none';
                bEl.style.display = 'none';
                if (nbEl.options.length > 1) nbEl.selectedIndex = 1;
            } else {
                nbWrap.style.display = 'block';
                bWrap.style.display = 'none';
                bEl.style.display = 'none';
            }
            mSyncTerm();
            const docsMap = {
                'Personal Loan': 'docs-personal-modal',
                'Emergency Loan': 'docs-emergency-modal',
                'Business Loan': 'docs-business-modal',
                'Education Loan': 'docs-education-modal',
            };
            ['personal', 'emergency', 'business', 'education'].forEach(t => {
                document.getElementById('docs-' + t + '-modal').style.display = 'none';
            });
            if (type) {
                document.getElementById('docs-wrapper-modal').style.display = 'block';
                document.getElementById(docsMap[type] || 'docs-personal-modal').style.display = 'block';
            } else {
                document.getElementById('docs-wrapper-modal').style.display = 'none';
            }
        }
        function mSyncTerm() {
            const bEl = document.getElementById('lending_type_term_business');
            const nbEl = document.getElementById('lending_type_term_nonbusiness');
            const hidden = document.getElementById('lending_type_term');
            hidden.value = (bEl.style.display === 'block') ? bEl.value : nbEl.value;
            // Clear term error when term syncs
            const termErr = document.getElementById('err-lending_type_term');
            if (hidden.value) {
                if (termErr) termErr.classList.remove('show');
                nbEl.classList.remove('field-error');
                bEl.classList.remove('field-error');
            }
            mCompute();
        }

        // ══════════════════════════════════════════════════════════
        //  PURPOSE TOGGLE
        // ══════════════════════════════════════════════════════════
        function mHandlePurpose(sel) {
            const wrap = document.getElementById('others-textarea-wrapper');
            wrap.style.display = sel.value === 'Others' ? 'block' : 'none';
            document.getElementById('purpose_loan_textarea').required = sel.value === 'Others';
        }

        // ══════════════════════════════════════════════════════════
        //  LOAN LIMIT CHECK
        // ══════════════════════════════════════════════════════════
        function mCheckLimit(input) {
            const warn = document.getElementById('loan-limit-warning');
            const val = parseFloat(input.value);
            const show = isNaN(val) || val <= 0 || val > MAX_REMAINING;
            warn.style.display = show ? 'block' : 'none';
            input.style.borderColor = show ? '#fca5a5' : '';
        }

        // ══════════════════════════════════════════════════════════
        //  LIVE INVOICE COMPUTE
        // ══════════════════════════════════════════════════════════
        function mCompute() {
            const typeEl = document.getElementById('lending_type');
            const type = typeEl.value;
            const fees = getSelectedFees();
            const a = parseFloat(document.getElementById('mLoanAmount').value) || 0;
            const termStr = document.getElementById('lending_type_term').value || '';
            const t = termStr ? parseInt(termStr) : 0;
            mUpdateAdjustCaption();

            const setV = (id, value, active) => {
                const el = document.getElementById(id);
                if (!el) return;
                el.textContent = value;
                active ? el.classList.remove('dim') : el.classList.add('dim');
            };

            setV('mis-type', type ? type : '—', !!type);
            setV('mis-amount', a > 0 ? fmt(a) : '—', a > 0);
            setV('mis-rate', fees ? (fees.rate * 100).toFixed(1) + '% / mo' : '—', !!fees);
            setV('mis-term', t > 0 ? t + ' months' : '—', t > 0);

            if (fees && a > 0 && t > 0) {
                const calc = mCalcBreakdown(a, fees, t, mIsAddSelected());

                setV('mis-mo', fmt(calc.monthlyPayment), true);
                setV('mis-int', fmt(calc.totalInterest), true);
                setV('mis-totfees', fmt(calc.totalFees), true);
                setV('mis-net', fmt(calc.netProceeds), true);
                setV('mis-total', fmt(calc.totalPayment), true);

                const firstInterest = Math.round((a * fees.rate) * 100) / 100;
                const firstFee = mIsAddSelected() ? Math.round((calc.totalFees / t) * 100) / 100 : 0;
                const firstInstallment = Math.round((calc.basePrincipal + firstInterest + firstFee) * 100) / 100;
                document.getElementById('mis-hint').textContent = 'Diminishing balance — starts at ₱' +
                    firstInstallment.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById('hidden-monthly').value = calc.monthlyPayment.toFixed(2);
                document.getElementById('hidden-total').value = calc.totalPayment.toFixed(2);
                document.getElementById('hidden-interest').value = calc.totalInterest.toFixed(2);
            } else {
                setV('mis-mo', '—', false);
                setV('mis-int', '—', false);
                setV('mis-totfees', '—', false);
                setV('mis-net', '—', false);
                setV('mis-total', '—', false);
                document.getElementById('mis-hint').textContent = 'Enter details to compute';
            }
        }

        // ══════════════════════════════════════════════════════════
        //  STEP 2 — VALIDATE & BUILD BREAKDOWN
        //  All alert() calls replaced with inline field errors
        // ══════════════════════════════════════════════════════════
        function mGoStep2() {
            const typeEl = document.getElementById('lending_type');
            const amountEl = document.getElementById('mLoanAmount');
            const termStr = document.getElementById('lending_type_term').value;
            const incEl = document.getElementById('mMonthlyIncome');
            const purEl = document.getElementById('purpose_loan_select');
            const purOthEl = document.getElementById('purpose_loan_textarea');

            const type = typeEl.value;
            const a = parseFloat(amountEl.value);
            const inc = incEl.value;
            const pur = purEl.value;

            let hasError = false;
            let firstErrorEl = null;

            // Clear all previous errors first
            [typeEl, amountEl, incEl, purEl, purOthEl].forEach(el => {
                el.classList.remove('field-error');
            });
            document.getElementById('lending_type_term_nonbusiness').classList.remove('field-error');
            document.getElementById('lending_type_term_business').classList.remove('field-error');
            ['err-lending_type', 'err-mLoanAmount', 'err-lending_type_term', 'err-mMonthlyIncome', 'err-purpose_loan_select', 'err-purpose_loan_textarea'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.remove('show');
            });

            // Validate: Loan Type
            if (!type) {
                mShowError(typeEl, 'err-lending_type');
                if (!firstErrorEl) firstErrorEl = typeEl;
                hasError = true;
            }

            // Validate: Loan Amount
            if (!amountEl.value || isNaN(a) || a <= 0) {
                mShowError(amountEl, 'err-mLoanAmount');
                if (!firstErrorEl) firstErrorEl = amountEl;
                hasError = true;
            } else if (a > MAX_REMAINING) {
                mShowError(amountEl, 'err-mLoanAmount');
                document.getElementById('err-mLoanAmount').querySelector
                    ? (document.getElementById('err-mLoanAmount').innerHTML = '<i class="fa fa-circle-exclamation"></i> Amount exceeds your borrowing limit.')
                    : null;
                if (!firstErrorEl) firstErrorEl = amountEl;
                hasError = true;
            } else if (SAVINGS_TO_LOAN_ENABLED && a > 0) {
                const requiredSavings = a + SAVINGS_HOLD_BACK;
                if (CURRENT_SAVINGS < requiredSavings) {
                    mShowError(amountEl, 'err-mLoanAmount');
                    document.getElementById('err-mLoanAmount').innerHTML = '<i class="fa fa-circle-exclamation"></i> You need ₱' + requiredSavings.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' in savings to borrow this amount (₱' + SAVINGS_HOLD_BACK.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' holdback must remain in savings).';
                    if (!firstErrorEl) firstErrorEl = amountEl;
                    hasError = true;
                }
            }

            // Validate: Loan Term
            if (!termStr) {
                const visibleTerm = document.getElementById('lending_type_term_business').style.display === 'block'
                    ? document.getElementById('lending_type_term_business')
                    : document.getElementById('lending_type_term_nonbusiness');
                mShowError(visibleTerm, 'err-lending_type_term');
                if (!firstErrorEl) firstErrorEl = visibleTerm;
                hasError = true;
            }

            // Validate: Monthly Income
            if (!inc) {
                mShowError(incEl, 'err-mMonthlyIncome');
                if (!firstErrorEl) firstErrorEl = incEl;
                hasError = true;
            }

            // Validate: Purpose
            if (!pur) {
                mShowError(purEl, 'err-purpose_loan_select');
                if (!firstErrorEl) firstErrorEl = purEl;
                hasError = true;
            }

            // Validate: Others textarea (if Others selected)
            if (pur === 'Others' && !purOthEl.value.trim()) {
                mShowError(purOthEl, 'err-purpose_loan_textarea');
                if (!firstErrorEl) firstErrorEl = purOthEl;
                hasError = true;
            }

            // Validate: Required documents
            const reqMap = {
                'Personal Loan': ['personal_valid_id', 'personal_proof_of_income'],
                'Emergency Loan': ['emergency_valid_id', 'emergency_proof_of_income', 'proof_of_emergency'],
                'Business Loan': ['business_valid_id', 'business_proof_of_income', 'business_permit'],
                'Education Loan': ['school_id', 'cor', 'education_valid_id'],
            };
            const reqFields = reqMap[type] || [];

            document.querySelectorAll('.upload-card-modal').forEach(c => {
                c.style.border = '';
                const e = c.querySelector('.uc-error');
                if (e) { e.style.display = 'none'; e.textContent = ''; }
            });

            let firstMissingCard = null;
            reqFields.forEach(name => {
                const inp = document.querySelector(`input[name="${name}"]`);
                if (inp && !inp.files.length) {
                    const card = inp.closest('.upload-card-modal');
                    if (card) {
                        card.style.border = '2px solid #dc2626';
                        let errEl = card.querySelector('.uc-error');
                        if (!errEl) {
                            errEl = document.createElement('div');
                            errEl.className = 'uc-error';
                            card.appendChild(errEl);
                        }
                        errEl.textContent = 'This document is required.';
                        errEl.style.display = 'block';
                        if (!firstMissingCard) firstMissingCard = card;
                    }
                    hasError = true;
                }
            });

            // Scroll to first error
            if (hasError) {
                const scrollTarget = firstErrorEl || firstMissingCard;
                if (scrollTarget) mFocusFirst(scrollTarget);
                return;
            }

            // All valid — build full charges breakdown (matches backend fee computation)
            const t = parseInt(termStr);
            const fees = getSelectedFees() ?? { rate: 0.02, processingRate: 0, serviceRate: 0, protectionPerMonth: 0, retentionRate: 0 };
            const calc = mCalcBreakdown(a, fees, t, mIsAddSelected());

            document.getElementById('cb-pri').textContent = fmt(a);
            document.getElementById('cb-rate').textContent = (fees.rate * 100).toFixed(1) + '% / mo';
            document.getElementById('cb-int').textContent = fmt(calc.totalInterest);
            document.getElementById('cb-term').textContent = t + ' months';
            document.getElementById('cb-proc').textContent = fmt(calc.processingFee);
            document.getElementById('cb-proc-hint').textContent = fees.processingRate + '% of principal';
            document.getElementById('cb-svc').textContent = fmt(calc.serviceFee);
            document.getElementById('cb-svc-hint').textContent = fees.serviceRate + '% of principal';
            document.getElementById('cb-protect').textContent = fmt(calc.protectionFee);
            document.getElementById('cb-protect-hint').textContent = '₱' + fees.protectionPerMonth + ' × ' + t + ' months';
            document.getElementById('cb-retention').textContent = fmt(calc.retentionFee);
            document.getElementById('cb-retention-hint').textContent = fees.retentionRate + '% of principal (unpaid rate)';
            document.getElementById('cb-charges').textContent = fmt(calc.totalInterest + calc.totalFees);
            const adjBox = document.getElementById('cb-adjust-box');
            if (adjBox) {
                if (calc.adjDelta > 0) {
                    adjBox.style.display = 'block';
                    document.getElementById('cb-adjust').textContent = '+ ' + fmt(calc.adjDelta);
                    document.getElementById('cb-adjust-hint').textContent = 'Charges added back — full loan released';
                } else {
                    adjBox.style.display = 'none';
                }
            }
            document.getElementById('cb-net').textContent = fmt(calc.netProceeds);
            document.getElementById('cb-total').textContent = fmt(calc.totalPayment);

            const tb = document.getElementById('amortBody');
            tb.innerHTML = '';
            const moPrinBase = Math.round((a / t) * 100) / 100;
            const moPrinLast = Math.round((a - moPrinBase * (t - 1)) * 100) / 100;
            let bal = a;
            for (let m = 1; m <= t; m++) {
                const moPrin = m === t ? moPrinLast : moPrinBase;      // final installment absorbs rounding
                const moInt = Math.round(bal * fees.rate * 100) / 100; // interest on remaining balance BEFORE this month's payment
                const moPayment = Math.round((moPrin + moInt) * 100) / 100; // this month's actual payment (varies, since interest declines)
                bal = Math.round((bal - moPrin) * 100) / 100;
                tb.innerHTML += `<tr>
                <td><span class="mo-n">${m}</span></td>
                <td><strong>${fmt(moPayment)}</strong></td>
                <td>${fmt(moPrin)}</td>
                <td class="int-n">${fmt(moInt)}</td>
                <td class="bal-n">${fmt(Math.max(bal, 0))}</td>
            </tr>`;
            }

            mGoStep(2);
        }

        // ══════════════════════════════════════════════════════════
        //  STEP 3 — POPULATE REVIEW
        // ══════════════════════════════════════════════════════════
        function mGoStep3() {
            const type = document.getElementById('lending_type').value;
            const a = parseFloat(document.getElementById('mLoanAmount').value);
            const termStr = document.getElementById('lending_type_term').value;
            const inc = parseFloat(document.getElementById('mMonthlyIncome').value) || 0;
            const pur = document.getElementById('purpose_loan_select').value;
            const purOth = document.getElementById('purpose_loan_textarea').value.trim();
            const t = parseInt(termStr);
            const fees = getSelectedFees() ?? { rate: 0.02, processingRate: 0, serviceRate: 0, protectionPerMonth: 0, retentionRate: 0 };
            const calc = mCalcBreakdown(a, fees, t, mIsAddSelected());

            // Application Summary
            document.getElementById('cf-type').textContent = type;
            document.getElementById('cf-amount').textContent = fmt(a);
            document.getElementById('cf-term').textContent = termStr;
            document.getElementById('cf-income').textContent = inc > 0 ? fmt(inc) : '—';
            document.getElementById('cf-purpose').textContent = pur === 'Others' ? (purOth || 'Others') : pur;
            document.getElementById('cf-rate').textContent = (fees.rate * 100).toFixed(1) + '% / mo';

            // Fees & Charges
            document.getElementById('cf-processing').textContent = '- ' + fmt(calc.processingFee);
            document.getElementById('cf-service').textContent = '- ' + fmt(calc.serviceFee);
            document.getElementById('cf-protection').textContent = '- ' + fmt(calc.protectionFee);
            document.getElementById('cf-retention').textContent = '- ' + fmt(calc.retentionFee);

            // Repayment Summary
            document.getElementById('cf-int').textContent = fmt(calc.totalInterest);
            document.getElementById('cf-monthly').textContent = fmt(calc.monthlyPayment);
            document.getElementById('cf-charges').textContent = fmt(calc.totalInterest + calc.totalFees);
            document.getElementById('cf-total').textContent = fmt(calc.totalPayment);

            // Total Proceeds (bottom card)
            document.getElementById('cf-tot-amount').textContent = fmt(a);
            document.getElementById('cf-totfees').textContent = '- ' + fmt(calc.totalFees);
            document.getElementById('cf-net').textContent = fmt(calc.netProceeds);

            // Adjustment rows (declared only ONCE)
            const cfAdjustRow = document.getElementById('cf-adjust-row');
            const cfTotAdjustRow = document.getElementById('cf-tot-adjust-row');
            if (calc.adjDelta > 0) {
                cfAdjustRow.style.display = 'flex';
                cfTotAdjustRow.style.display = 'flex';
                document.getElementById('cf-adjust').textContent = '+ ' + fmt(calc.adjDelta);
                document.getElementById('cf-tot-adjust').textContent = '+ ' + fmt(calc.adjDelta);
            } else {
                cfAdjustRow.style.display = 'none';
                cfTotAdjustRow.style.display = 'none';
            }

            mGoStep(3);
        }


        // ══════════════════════════════════════════════════════════
        //  SUBMIT — inline error for unchecked checkbox
        // ══════════════════════════════════════════════════════════
        function mSubmit() {
            if (!document.getElementById('mAgree').checked) {
                document.getElementById('agreeRow').classList.add('cb-error');
                document.getElementById('err-mAgree').classList.add('show');
                document.getElementById('agreeRow').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            document.getElementById('loan-form').submit();
        }

        // ══════════════════════════════════════════════════════════
        //  FILE UPLOAD FEEDBACK
        // ══════════════════════════════════════════════════════════
        function mOnFileSelected(input, cardId, nameId) {
            const card = document.getElementById(cardId);
            const nameEl = document.getElementById(nameId);
            if (!card || !nameEl) return;

            const badge = card.querySelector('.uc-badge');

            if (input.files && input.files[0]) {
                card.classList.add('has-file');
                card.style.border = '';
                const errEl = card.querySelector('.uc-error');
                if (errEl) { errEl.style.display = 'none'; }
                nameEl.textContent = input.files[0].name;

                if (badge) {
                    badge.textContent = 'Uploaded';
                    badge.classList.remove('required', 'optional');
                    badge.classList.add('uploaded');
                }
            } else {
                card.classList.remove('has-file');
                nameEl.textContent = '';

                if (badge) {
                    // restore original label — store it on first render
                    const original = badge.dataset.original || 'Required';
                    badge.textContent = original;
                    badge.classList.remove('uploaded');
                    badge.classList.add(original.toLowerCase());
                }
            }
        }
    </script>

    <script nonce="{{ csp_nonce() }}">
        const PAGE_SIZE = 10;
        const pgState = {}; // { listId: currentPage }

        function toggleLoanCard(rowEl) {
            const card = rowEl.closest('.loan-card');
            if (!card) return;
            card.classList.toggle('open');
        }

        function getFilteredCards(listId) {
            const list = document.getElementById(listId);
            if (!list) return [];
            return Array.from(list.querySelectorAll('.loan-card'))
                .filter(card => card.dataset.filteredOut !== 'true');
        }

        function paginateList(listId) {
            const list = document.getElementById(listId);
            if (!list) return;

            const allCards = Array.from(list.querySelectorAll('.loan-card'));
            const filtered = allCards.filter(card => card.dataset.filteredOut !== 'true');

            const totalPages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
            let page = pgState[listId] || 1;
            if (page > totalPages) page = totalPages;
            pgState[listId] = page;

            // Hide anything the filters excluded
            allCards.forEach(card => {
                if (card.dataset.filteredOut === 'true') {
                    card.style.display = 'none';
                }
            });

            // Show only the current page of what's left
            filtered.forEach((card, i) => {
                const onPage = Math.floor(i / PAGE_SIZE) + 1 === page;
                card.style.display = onPage ? '' : 'none';
            });

            renderPaginationControls(listId, page, totalPages, filtered.length);
        }

        function renderPaginationControls(listId, page, totalPages, total) {
            const container = document.getElementById(listId + '-pg');
            const shownEl = document.getElementById(listId + '-shown');
            if (shownEl) {
                const shownCount = total === 0 ? 0 : Math.min(PAGE_SIZE, total - (page - 1) * PAGE_SIZE);
                shownEl.textContent = shownCount;
            }
            if (!container) return;
            container.innerHTML = '';
            if (totalPages <= 1) return; // no controls needed for a single page

            const goTo = (targetPage) => {
                pgState[listId] = targetPage;
                paginateList(listId);
            };

            // ‹ Previous
            const prevBtn = document.createElement('button');
            prevBtn.type = 'button';
            prevBtn.className = 'pg-btn';
            prevBtn.setAttribute('aria-label', 'Previous page');
            prevBtn.innerHTML = '<i class="fa fa-chevron-left"></i>';
            prevBtn.disabled = page <= 1;
            prevBtn.addEventListener('click', () => goTo(page - 1));

            // "2 of 4"
            const info = document.createElement('span');
            info.className = 'pg-info';
            info.textContent = page + ' of ' + totalPages;

            // Next ›
            const nextBtn = document.createElement('button');
            nextBtn.type = 'button';
            nextBtn.className = 'pg-btn';
            nextBtn.setAttribute('aria-label', 'Next page');
            nextBtn.innerHTML = '<i class="fa fa-chevron-right"></i>';
            nextBtn.disabled = page >= totalPages;
            nextBtn.addEventListener('click', () => goTo(page + 1));

            container.appendChild(prevBtn);
            container.appendChild(info);
            container.appendChild(nextBtn);
        }

        function filterCards(listId, query) {
            const list = document.getElementById(listId);
            if (!list) return;
            const q = query.toLowerCase().trim();
            list.querySelectorAll('.loan-card').forEach(card => {
                const matches = (q === '' || card.textContent.toLowerCase().includes(q));
                card.dataset.filteredOut = matches ? 'false' : 'true';
            });
            pgState[listId] = 1;
            paginateList(listId);
        }


        document.addEventListener('DOMContentLoaded', () => {
            ['all-loans-list', 'due-today-list', 'due-week-list', 'overdue-list'].forEach(id => {
                if (document.getElementById(id)) paginateList(id);
            });
        });
    </script>

</body>

</html>