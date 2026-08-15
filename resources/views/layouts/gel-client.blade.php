<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'GEL Entreprise') — Portail Client</title>

  {{-- Fonts + Icons + Bootstrap --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    /* ═══════════════════════════════════════════════════════════════
       RESET & VARIABLES
    ═══════════════════════════════════════════════════════════════ */
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    :root {
      --gel-primary: #2CA01C;
      --gel-primary-hover: #1D7C13;
      --gel-primary-light: #EBF7E9;
      --gel-sidebar-bg: var(--gel-primary);
      --gel-sidebar-hover: #F4F5F8;
      --gel-sidebar-active: #EBF7E9;
      --gel-sidebar-width: 240px;
      --gel-topbar-height: 56px;
      --gel-text-primary: #1F2A44;
      --gel-text-secondary: #6B6C72;
      --gel-text-muted: #9CA3AF;
      --gel-border: #D1D5DB;
      --gel-card-bg: #FFFFFF;
      --gel-bg: #F4F5F8;
      --gel-success: #10B981;
      --gel-danger: #EF4444;
      --gel-warning: #F59E0B;
      --gel-info: #3B82F6;
      --gel-dropdown-shadow: 0 8px 24px rgba(0,0,0,0.15);
      --gel-font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    html { font-size: 14px; }
    body {
      font-family: var(--gel-font);
      color: var(--gel-text-primary);
      background: #F4F5F8;
      min-height: 100vh;
    }

    a { text-decoration: none; color: inherit; }
    button { cursor: pointer; font-family: inherit; }
    img { max-width: 100%; }

    /* ═══════════════════════════════════════════════════════════════
       TOPBAR
    ═══════════════════════════════════════════════════════════════ */
    .gel-topbar {
      position: fixed; top: 0; left: var(--gel-sidebar-width); right: 0;
      height: var(--gel-topbar-height);
      background: var(--gel-primary);
      border-bottom: 1px solid var(--gel-primary-hover);
      display: flex; align-items: center;
      padding: 0 20px;
      z-index: 1000;
    }

    .topbar-logo {
      font-weight: 700; font-size: 18px;
      color: white; margin-right: 16px;
      display: flex; align-items: center;
    }
    .topbar-logo small {
      font-weight: 400; font-size: 12px;
      color: rgba(255,255,255,0.7); margin-left: 6px;
    }

    .btn-go-business {
      display: flex; align-items: center; gap: 6px;
      padding: 6px 12px;
      border: 1px solid rgba(255,255,255,0.4);
      border-radius: 4px; background: transparent; color: white;
      cursor: pointer; font-size: 13px; font-weight: 500;
      position: relative; white-space: nowrap;
      transition: background 120ms;
    }
    .btn-go-business:hover { background: rgba(255,255,255,0.15); }

    .search-bar {
      flex: 1; max-width: 360px; margin: 0 20px; position: relative;
    }
    .search-bar input {
      width: 100%;
      padding: 8px 12px 8px 34px;
      border: 1px solid rgba(255,255,255,0.3);
      border-radius: 6px; font-size: 13px;
      background: var(--gel-sidebar-bg);
      color: white;
      transition: all 150ms; outline: none;
    }
    .search-bar input::placeholder {
      color: rgba(255,255,255,0.7);
    }
    .search-bar input:focus {
      border-color: var(--gel-primary);
      background: white;
      color: var(--gel-text-primary);
      box-shadow: 0 0 0 3px rgba(0,91,172,0.1);
    }
    .search-bar input:focus::placeholder {
      color: var(--gel-text-muted);
    }
    .search-icon {
      position: absolute; left: 10px; top: 50%;
      transform: translateY(-50%);
      color: rgba(255,255,255,0.7); font-size: 14px;
      pointer-events: none;
      transition: color 150ms;
    }
    .search-bar:focus-within .search-icon {
      color: var(--gel-text-secondary);
    }
    .search-kbd {
      position: absolute; right: 8px; top: 50%;
      transform: translateY(-50%);
      font-size: 10px; color: rgba(255,255,255,0.8);
      background: rgba(255,255,255,0.1); padding: 1px 5px;
      border-radius: 3px; border: 1px solid rgba(255,255,255,0.3);
      transition: all 150ms;
    }
    .search-bar:focus-within .search-kbd {
      color: var(--gel-text-muted);
      background: var(--gel-bg);
      border-color: var(--gel-border);
    }

    .topbar-right {
      display: flex; align-items: center; gap: 4px; margin-left: auto;
    }
    .topbar-btn {
      width: 34px; height: 34px; border: none; background: none;
      border-radius: 50%; cursor: pointer;
      color: white; font-size: 17px;
      display: flex; align-items: center; justify-content: center;
      position: relative; transition: background 120ms;
    }
    .topbar-btn:hover { background: rgba(255,255,255,0.15); }

    .notif-dot::after {
      content: ''; position: absolute; top: 5px; right: 5px;
      width: 7px; height: 7px; background: var(--gel-danger);
      border-radius: 50%; border: 2px solid white;
    }

    .avatar {
      width: 34px; height: 34px; border-radius: 50%;
      background: white; color: var(--gel-primary);
      display: flex; align-items: center; justify-content: center;
      font-weight: 600; font-size: 13px; cursor: pointer;
      margin-left: 4px;
      flex-shrink: 0;
    }

    /* ═══════════════════════════════════════════════════════════════
       SIDEBAR
    ═══════════════════════════════════════════════════════════════ */
    .gel-sidebar::-webkit-scrollbar { display: none; }
    .gel-sidebar {
      position: fixed; top: 0; left: 0;
      width: var(--gel-sidebar-width); height: 100vh;
      background: var(--gel-sidebar-bg);
      
      
      overflow-y: auto;
      -ms-overflow-style: none;
      scrollbar-width: none; z-index: 999;
      display: flex; flex-direction: column;
    }

    .btn-nouveau-sidebar {
      width: 100%;
      background: var(--gel-primary);
      color: white;
      border: none;
      border-radius: 20px;
      padding: 10px 16px;
      font-size: 14px;
      font-weight: 600;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      cursor: pointer;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      transition: background 150ms, box-shadow 150ms;
    }
    .btn-nouveau-sidebar:hover {
      background: var(--gel-primary-hover);
      box-shadow: 0 4px 6px rgba(0,0,0,0.15);
    }

    .sidebar-menu { list-style: none; padding: 8px; margin: 0; flex: 1; }

    .sidebar-item {
      display: flex; align-items: center;
      padding: 10px 14px; cursor: pointer;
      font-size: 13.5px; color: rgba(255,255,255,0.75);
      border-radius: 20px 0 0 20px; margin: 0 0 4px 12px;
      position: relative; user-select: none;
      transition: all 120ms ease;
    }
    .sidebar-item:hover { background:rgba(255,255,255,0.1); color:white; }
        .sidebar-item.active {
      background: var(--gel-bg);
      color: var(--gel-primary); font-weight: 600;
    }
    .sidebar-item.active::before,
    .sidebar-item.active::after {
        content: '';
        position: absolute;
        right: 0;
        width: 20px;
        height: 20px;
        background: transparent;
        pointer-events: none;
    }
    .sidebar-item.active::before {
        bottom: 100%;
        border-bottom-right-radius: 20px;
        box-shadow: 10px 10px 0 10px var(--gel-bg);
    }
    .sidebar-item.active::after {
        top: 100%;
        border-top-right-radius: 20px;
        box-shadow: 10px -10px 0 10px var(--gel-bg);
    }
    .sidebar-item .arrow {
      margin-left: auto; font-size: 10px;
      color: var(--gel-text-secondary); transition: none;
    }

    .sidebar-section {
      font-size: 11px; font-weight: 600; color: var(--gel-text-muted);
      text-transform: uppercase; letter-spacing: 0.5px;
      padding: 16px 12px 6px;
    }

    /* ═══════════════════════════════════════════════════════════════
       NESTED DROPDOWN
    ═══════════════════════════════════════════════════════════════ */
    .nested-dropdown {
      position: fixed;
      left: calc(var(--gel-sidebar-width) + 4px);
      top: 100px;
      background: white;
      border: 1px solid var(--gel-border);
      border-radius: 8px;
      box-shadow: var(--gel-dropdown-shadow);
      min-width: 230px;
      padding: 6px 0;
      opacity: 0; visibility: hidden;
      transform: translateY(-4px);
      transition: opacity 120ms ease, visibility 120ms ease, transform 120ms ease;
      z-index: 1100;
      pointer-events: none;
    }
    .nested-dropdown.open {
      opacity: 1; visibility: visible;
      transform: translateY(0);
      pointer-events: auto;
    }

    .mega-menu {
      position: fixed;
      left: calc(var(--gel-sidebar-width) + 4px);
      top: 60px;
      background: white;
      border: 1px solid var(--gel-border);
      border-radius: 8px;
      box-shadow: var(--gel-dropdown-shadow);
      display: flex;
      gap: 32px;
      padding: 24px;
      opacity: 0; visibility: hidden;
      transform: translateY(-4px);
      transition: opacity 120ms ease, visibility 120ms ease, transform 120ms ease;
      z-index: 1100;
      pointer-events: none;
    }
    .mega-menu.open {
      opacity: 1; visibility: visible;
      transform: translateY(0);
      pointer-events: auto;
    }
    .mega-col {
      min-width: 160px;
    }
    .mega-header {
      font-size: 12px; font-weight: 700; color: var(--gel-text-primary);
      text-transform: uppercase; letter-spacing: 0.5px;
      padding-bottom: 8px;
      border-bottom: 1px solid var(--gel-border);
      margin-bottom: 12px;
    }
    .mega-item {
      display: flex; align-items: center; gap: 8px;
      padding: 6px 0; cursor: pointer;
      font-size: 13px; color: var(--gel-text-secondary);
      text-decoration: none;
    }
    .mega-item:hover { color: var(--gel-primary); }
    .mega-icon { width: 16px; text-align: center; color: var(--gel-text-muted); font-size: 14px; }

    .dd-header {
      padding: 6px 14px 4px;
      font-size: 11px; font-weight: 600; color: var(--gel-text-muted);
      text-transform: uppercase; letter-spacing: 0.5px;
    }

    .dd-item {
      display: flex; align-items: center;
      padding: 8px 14px; cursor: pointer;
      font-size: 13px; color: var(--gel-text-primary);
      white-space: nowrap; transition: background 80ms;
    }
    .dd-item:hover { background: #F0F4F8; }
    .dd-item.active { background: var(--gel-primary-light); color: var(--gel-primary); font-weight: 600; }
    .dd-item .arrow { margin-left: auto; font-size: 10px; color: var(--gel-text-secondary); }
    .dd-item .dd-icon { width: 20px; text-align: center; margin-right: 8px; font-size: 14px; }

    .dd-divider { height: 1px; background: var(--gel-border); margin: 4px 0; }

    /* ═══════════════════════════════════════════════════════════════
       CONTENT
    ═══════════════════════════════════════════════════════════════ */
    .gel-content {
      margin-left: var(--gel-sidebar-width);
      margin-top: var(--gel-topbar-height);
      padding: 28px 32px;
      min-height: calc(100vh - var(--gel-topbar-height));
      background: #ECEEF1;
    }

    /* ═══════════════════════════════════════════════════════════════
       COMPOSANTS — KPI, CARDS, BOUTONS
    ═══════════════════════════════════════════════════════════════ */

    /* KPI Grid */
    .gel-kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 16px;
      margin-bottom: 24px;
    }

    .gel-kpi-card {
      background: white;
      border: 1px solid var(--gel-border);
      border-radius: 8px;
      padding: 18px 20px;
    }

    .gel-kpi-label {
      font-size: 12px;
      font-weight: 600;
      color: var(--gel-text-secondary);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .gel-kpi-value {
      font-size: 24px;
      font-weight: 700;
      margin: 8px 0 4px;
      color: var(--gel-text-primary);
    }

    .gel-kpi-change {
      font-size: 13px;
    }
    .gel-kpi-change.up { color: var(--gel-success); }
    .gel-kpi-change.down { color: var(--gel-danger); }

    /* Card générique */
    .gel-card {
      background: white;
      border: 1px solid var(--gel-border);
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.02);
      transition: all 200ms ease;
    }
    .gel-card:hover {
      box-shadow: 0 8px 24px rgba(0,0,0,0.06);
      border-color: rgba(0,0,0,0.1);
      transform: translateY(-2px);
    }
    .gel-card-header {
      padding: 14px 18px;
      border-bottom: 1px solid var(--gel-border);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .gel-card-body { padding: 18px; }

    /* Table */
    .gel-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }
    .gel-table th {
      text-align: left;
      padding: 10px 14px;
      border-bottom: 1px solid var(--gel-border);
      color: var(--gel-text-secondary);
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .gel-table td {
      padding: 10px 14px;
      border-bottom: 1px solid #F0F4F8;
    }

    /* Badges */
    .gel-badge {
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
    }
    .gel-badge-success {
      background: #ECFDF5;
      color: var(--gel-success);
    }
    .gel-badge-warning {
      background: #FFFBEB;
      color: var(--gel-warning);
    }

    /* Boutons */
    .gel-btn {
      display: inline-flex; align-items: center; justify-content: center; gap: 6px;
      padding: 8px 16px; border-radius: 6px;
      font-size: 13px; font-weight: 600;
      border: none; cursor: pointer;
      transition: all 200ms ease;
    }
    .gel-btn-sm { padding: 5px 10px; font-size: 12px; }
    .gel-btn-primary { 
      background: var(--gel-primary); color: white !important; 
      box-shadow: 0 2px 4px rgba(13, 148, 136, 0.2);
    }
    .gel-btn-primary:hover { 
      background: var(--gel-primary-hover); 
      box-shadow: 0 4px 8px rgba(13, 148, 136, 0.3);
      transform: translateY(-1px);
    }
    .gel-btn-secondary {
      background: white; color: var(--gel-text-primary);
      border: 1px solid var(--gel-border);
      box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .gel-btn-secondary:hover { 
      background: #f8fafc; 
      border-color: #cbd5e1;
      transform: translateY(-1px);
    }

    .gel-filter-select {
      padding: 5px 10px; border: 1px solid var(--gel-border);
      border-radius: 4px; font-size: 13px;
      background: white; font-family: inherit;
      color: var(--gel-text-primary);
    }

    /* ─── Dropdown ─── */
    .gel-dropdown { position: relative; }
    .gel-dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border: 1px solid var(--gel-border);
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        min-width: 180px;
        z-index: 50;
        padding: 4px;
        display: none;
    }
    .gel-dropdown-menu.show { display: block; }
    .gel-dropdown-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        font-size: 13px;
        color: var(--gel-text-primary);
        text-decoration: none;
        border-radius: 4px;
        cursor: pointer;
        border: none;
        background: none;
        width: 100%;
        font-family: inherit;
        text-align: left;
    }
    .gel-dropdown-item:hover { background: #f4f5f8; }
    .gel-dropdown-item i { width: 16px; color: var(--gel-text-secondary); text-align: center; }
    .gel-dropdown-divider {
        height: 1px;
        background: var(--gel-border);
        margin: 4px 0;
    }

    /* Chart containers */
    .gel-chart-container {
      background: white;
      border: 1px solid var(--gel-border);
      border-radius: 8px;
      padding: 18px;
    }
    .gel-chart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
    }
    .gel-chart-title {
      font-size: 15px;
      font-weight: 600;
    }

    /* Alertes */
    .gel-alertes {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-bottom: 24px;
    }
    .gel-alerte-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 16px;
      border-radius: 6px;
      font-size: 13px;
      border-left: 3px solid;
    }
    .gel-alerte-item.alerte-jaune {
      background: #FFFBEB;
      border-left-color: var(--gel-warning);
    }
    .gel-alerte-item.alerte-rouge {
      background: #FEF2F2;
      border-left-color: var(--gel-danger);
    }
    .gel-alerte-item.alerte-verte {
      background: #ECFDF5;
      border-left-color: var(--gel-success);
    }

    /* Échéances */
    .gel-echeances { margin: 0; }
    .gel-echeance-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 18px;
      border-bottom: 1px solid #F0F4F8;
    }
    .gel-echeance-item:last-child { border-bottom: none; }
    .gel-echeance-label { font-size: 13px; display: flex; align-items: center; gap: 8px; }
    .gel-echeance-date {
      font-size: 12px;
      font-weight: 600;
      color: var(--gel-text-secondary);
    }

    /* Empty state */
    .gel-empty {
      text-align: center;
      padding: 40px;
      color: var(--gel-text-muted);
    }
    .gel-empty i {
      font-size: 40px;
      margin-bottom: 12px;
      display: block;
    }
    .gel-empty h3 {
      font-size: 16px;
      font-weight: 600;
      color: var(--gel-text-primary);
    }
    .gel-empty p {
      font-size: 13px;
    }

    /* Page header */
    .gel-page-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
    }
    .gel-page-title {
      font-size: 22px;
      font-weight: 700;
      color: var(--gel-text-primary);
    }
    .gel-page-subtitle {
      font-size: 13px;
      color: var(--gel-text-secondary);
      margin-top: 2px;
    }

    /* ═══════════════════════════════════════════════════════════════
       DOCUMENTS (FORMULAIRES & LIGNES)
    ═══════════════════════════════════════════════════════════════ */
    .doc-form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
    .doc-form-group, .gel-form-group { display: flex; flex-direction: column; margin-bottom: 14px; }
    .doc-label, .gel-form-group label { font-size: 13px; font-weight: 600; color: var(--gel-text-primary); margin-bottom: 6px; }
    .doc-input, .gel-form-control, .gel-form-select { padding: 9px 12px; border: 1px solid var(--gel-border); border-radius: 6px; font-size: 14px; font-family: var(--gel-font); transition: border-color 150ms; background-color: #fff; width: 100%; box-sizing: border-box; }
    .doc-input:focus, .gel-form-control:focus, .gel-form-select:focus { outline: none; border-color: var(--gel-primary); box-shadow: 0 0 0 3px rgba(0,91,172,0.1); }
    textarea.doc-input, textarea.gel-form-control { resize: vertical; }

    .doc-lines-table { width: 100%; border-collapse: collapse; }
    .doc-lines-table th { background: var(--gel-sidebar-bg); padding: 10px 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: white; text-align: left; border-bottom: 1px solid var(--gel-border); }
    .doc-lines-table td { padding: 8px 10px; border-bottom: 1px solid var(--gel-border); vertical-align: middle; font-size: 14px; }
    .doc-line-row.selected { background: rgba(59,130,246,0.05); }
    .doc-input-sm { width: 100%; padding: 7px 8px; border: 1px solid var(--gel-border); border-radius: 4px; font-size: 13px; font-family: var(--gel-font); }
    .doc-input-sm:focus { outline: none; border-color: var(--gel-primary); }
    .doc-line-remove { background: none; border: none; color: var(--gel-text-muted); font-size: 14px; padding: 6px; border-radius: 4px; cursor: pointer; }
    .doc-line-remove:hover { background: rgba(239,68,68,0.1); color: var(--gel-danger); }
    
    .doc-summary-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--gel-border); font-size: 14px; }
    .doc-summary-total { font-weight: 700; font-size: 16px; border-bottom: none; padding-top: 12px; color: var(--gel-primary); }
    .line-num { font-weight: 600; color: var(--gel-text-muted); text-align: center; }

    /* ═══════════════════════════════════════════════════════════════
       TOAST
    ═══════════════════════════════════════════════════════════════ */
    /* ═══════════════════════════════════════════════════════════════
       TOAST SYSTEM (Style Épuré SaaS — Positionné en bas à droite)
    ═══════════════════════════════════════════════════════════════ */
    .gel-toast-container {
      position: fixed;
      bottom: 24px;
      right: 24px;
      z-index: 99999;
      display: flex;
      flex-direction: column;
      gap: 12px;
      pointer-events: none;
    }
    .gel-toast {
      pointer-events: auto;
      position: relative;
      background: #ffffff;
      color: #0f172a;
      padding: 16px 20px;
      border-radius: 12px;
      border: 1px solid #e2e8f0;
      border-left: 4px solid #10b981;
      box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1), 0 4px 12px rgba(0, 0, 0, 0.04);
      display: flex;
      align-items: flex-start;
      gap: 14px;
      font-size: 13.5px;
      min-width: 330px;
      max-width: 430px;
      overflow: hidden;
      animation: toastSlideUp 350ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    /* ═══════════════════════════════════════════════════════════════
       PRINT STYLES FOR PDF EXPORT
    ═══════════════════════════════════════════════════════════════ */
    @media print {
      body { background: white !important; }
      .gel-sidebar, .gel-topbar, .gel-toast-container, button.gel-btn { display: none !important; }
      .gel-main { margin-left: 0 !important; padding: 0 !important; }
      .gel-card { box-shadow: none !important; border: none !important; padding: 0 !important; }
      @page { margin: 1cm; }
    }
    .gel-toast-progress {
      position: absolute;
      bottom: 0; left: 0;
      height: 3px;
      background: #10b981;
      opacity: 0.25;
      width: 100%;
      animation: toastProgress 4.5s linear forwards;
    }
    .gel-toast-icon-wrapper {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: #ecfdf5;
      color: #10b981;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      flex-shrink: 0;
    }
    .gel-toast-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 2px;
    }
    .gel-toast-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 2px;
    }
    .gel-toast-ai-badge {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #059669;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }
    .gel-toast-message {
      color: #334155;
      font-weight: 500;
      line-height: 1.45;
    }
    .gel-toast-close {
      background: none;
      border: none;
      color: #94a3b8;
      font-size: 18px;
      cursor: pointer;
      padding: 0;
      line-height: 1;
      transition: color 0.2s;
    }
    .gel-toast-close:hover { color: #475569; }

    /* Variantes par type */
    .gel-toast-error { border-left-color: #ef4444; }
    .gel-toast-error .gel-toast-icon-wrapper { background: #fef2f2; color: #ef4444; }
    .gel-toast-error .gel-toast-ai-badge { color: #dc2626; }
    .gel-toast-error .gel-toast-progress { background: #ef4444; }

    .gel-toast-warning { border-left-color: #f59e0b; }
    .gel-toast-warning .gel-toast-icon-wrapper { background: #fffbeB; color: #f59e0b; }
    .gel-toast-warning .gel-toast-ai-badge { color: #d97706; }
    .gel-toast-warning .gel-toast-progress { background: #f59e0b; }

    .gel-toast-info { border-left-color: #3b82f6; }
    .gel-toast-info .gel-toast-icon-wrapper { background: #eff6ff; color: #3b82f6; }
    .gel-toast-info .gel-toast-ai-badge { color: #2563eb; }
    .gel-toast-info .gel-toast-progress { background: #3b82f6; }

    .gel-toast.exit {
      animation: toastSlideDown 250ms cubic-bezier(0.4, 0, 1, 1) forwards;
    }

    @keyframes toastSlideUp {
      from { transform: translateY(60px) scale(0.95); opacity: 0; }
      to { transform: translateY(0) scale(1); opacity: 1; }
    }
    @keyframes toastSlideDown {
      from { transform: translateY(0) scale(1); opacity: 1; }
      to { transform: translateY(40px) scale(0.95); opacity: 0; }
    }
    @keyframes aiGlowLine {
      0% { background-position: 0% 50%; }
      100% { background-position: 200% 50%; }
    }
    @keyframes toastProgress {
      from { width: 100%; }
      to { width: 0%; }
    }

    /* ═══════════════════════════════════════════════════════════════
       OVERLAY + SLIDE PANEL
    ═══════════════════════════════════════════════════════════════ */
    .panel-overlay {
      position: fixed; top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.3);
      z-index: 2000; opacity: 0; visibility: hidden;
      transition: all 250ms;
    }
    .panel-overlay.open { opacity: 1; visibility: visible; }

    .slide-panel {
      position: fixed; top: 0; right: 0;
      width: 480px; height: 100vh;
      background: white; z-index: 2001;
      box-shadow: -4px 0 24px rgba(0,0,0,0.15);
      transform: translateX(100%);
      transition: transform 250ms ease;
      overflow-y: auto;
    }
    .slide-panel.open { transform: translateX(0); }

    .panel-header {
      display: flex; align-items: center;
      justify-content: space-between;
      padding: 18px 24px;
      border-bottom: 1px solid var(--gel-border);
    }
    .panel-header h3 { font-size: 17px; font-weight: 600; }
    .panel-close {
      width: 32px; height: 32px; border: none;
      background: none; font-size: 20px;
      cursor: pointer; color: var(--gel-text-secondary);
      border-radius: 50%; display: flex;
      align-items: center; justify-content: center;
    }
    .panel-close:hover { background: var(--gel-sidebar-hover); }
    .panel-body { padding: 24px; }

    /* User footer dans la sidebar */
    .sidebar-footer {
      padding: 12px;
      border-top: 1px solid var(--gel-border);
      display: flex;
      align-items: center;
      gap: 10px;
      flex-shrink: 0;
    }

    /* ═══════════════════════════════════════════════════════════════
       RESPONSIVE
    ═══════════════════════════════════════════════════════════════ */
    @media (max-width: 768px) {
      :root { --gel-sidebar-width: 0px; }
      .gel-sidebar::-webkit-scrollbar { display: none; }
    .gel-sidebar { display: none; }
      .gel-topbar { left: 0; }
      .gel-content { margin-left: 0; }
      .slide-panel { width: 100%; }
      .gel-kpi-grid { grid-template-columns: 1fr 1fr; }
    }
  </style>

  @stack('styles')
</head>
<body>

  {{-- ════════════════════════════════════════════ TOPBAR ═══════════════ --}}
  <header class="gel-topbar">
    <div style="display:flex;align-items:center;gap:12px;">
      <div class="topbar-logo">
        <span>GEL <small>Entreprise</small></span>
      </div>

    {{-- Go To Business / Select Client --}}
      <div style="position:relative;">
        <a href="{{ route('dashboard') }}" class="btn-go-business" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
          <i class="fas fa-exchange-alt"></i> 
          Changer d'entreprise
        </a>
      </div>
    </div>

    {{-- Search --}}
    <div class="search-bar">
      <i class="fas fa-search search-icon"></i>
      <input type="text" placeholder="Rechercher... Ctrl+K" id="globalSearch"
             autocomplete="off"
             oninput="handleGlobalSearch(this.value)"
             onfocus="handleGlobalSearch(this.value)"
             onblur="setTimeout(closeSearchResults, 250)">
      <span class="search-kbd">Ctrl+K</span>
      <div class="nested-dropdown" id="searchResults"
           style="position:absolute;left:0;top:calc(100% + 6px);width:100%;min-width:400px;max-height:480px;overflow-y:auto;">
        {{-- Contenu initial : raccourcis --}}
        <div id="searchDefaultContent">
          <div class="dd-header">RACCOURCIS</div>
          <div class="dd-item" onclick="navigateTo('dashboard')"><span class="dd-icon"><i class="fas fa-tachometer-alt me-1"></i></span> Tableau de bord</div>
          <div class="dd-item" onclick="navigateTo('clients')"><span class="dd-icon"><i class="fas fa-users me-1"></i></span> Mes clients</div>
          <div class="dd-item" onclick="navigateTo('ecritures')"><span class="dd-icon"><i class="fas fa-pen-fancy me-1"></i></span> Saisir une écriture</div>
          <div class="dd-item" onclick="navigateTo('plan-comptable')"><span class="dd-icon"><i class="fas fa-list-ol me-1"></i></span> Plan comptable</div>
          <div class="dd-item" onclick="navigateTo('factures')"><span class="dd-icon"><i class="fas fa-file-invoice me-1"></i></span> Factures</div>
        </div>
        {{-- Résultats AJAX --}}
        <div id="searchAjaxResults" style="display:none;"></div>
        {{-- Loader --}}
        <div id="searchLoader" style="display:none;padding:20px;text-align:center;color:rgba(255,255,255,0.5);">
          <i class="fas fa-spinner fa-spin me-1"></i> Recherche en cours...
        </div>
      </div>
    </div>

    {{-- Right --}}
    <div class="topbar-right">
      <button class="topbar-btn" title="Fil d'actualité" onclick="openActivityFeed()"><i class="fas fa-rss"></i></button>
      <button class="topbar-btn" title="Aide" onclick="openHelpPanel()"><i class="fas fa-question-circle"></i></button>

      {{-- Notifications --}}
      @php
        $notifications = collect();
        $docTransmisEnAttente = 0;
      @endphp
      <div style="position:relative;">
        <button class="topbar-btn" onclick="toggleDropdown('notifDropdown')" title="Notifications" style="position:relative;">
          <i class="fas fa-bell"></i>
          @if($notifications->count() > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="font-size: 8px; padding: 3px 5px; margin-top: 5px;">{{ $notifications->count() }}</span>
          @endif
        </button>
        <div class="nested-dropdown" id="notifDropdown"
             style="position:absolute;right:0;left:auto;top:calc(100% + 6px);min-width:360px;">
          <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px 6px;border-bottom:1px solid var(--gel-border);">
            <span style="font-weight:600;font-size:14px;">Notifications</span>
            <a href="#" style="font-size:12px;font-weight:500;color:var(--gel-primary);" onclick="clearAllNotifications()">Tout marquer</a>
          </div>
          @forelse($notifications as $notif)
            <a href="{{ $notif['url'] }}" class="dd-item notif-item" style="gap:10px;white-space:normal;cursor:pointer;text-decoration:none;color:inherit;">
              <span><i class="{{ $notif['icon'] }} me-1"></i></span>
              <div><div>{{ $notif['text'] }}</div><div style="font-size:11px;color:rgba(255,255,255,0.5);">{{ $notif['time'] }}</div></div>
            </a>
          @empty
            <div class="dd-item" style="justify-content:center;color:rgba(255,255,255,0.5);padding:20px;">
              <i class="fas fa-inbox me-1"></i> Aucune notification
            </div>
          @endforelse
          @if($notifications->count() > 0)
            <div class="dd-divider"></div>
            <div class="dd-item" style="justify-content:center;color:var(--gel-primary);font-weight:500;">Voir toutes →</div>
          @endif
        </div>
      </div>

      <a href="#" class="topbar-btn" title="Paramètres"><i class="fas fa-cog"></i></a>

      <div style="position:relative;">
        <div class="avatar" onclick="toggleDropdown('userDropdown')" style="overflow:hidden;display:flex;align-items:center;justify-content:center;">
          @if(auth()->user()?->photo)
            <img src="{{ asset('storage/' . auth()->user()->photo) }}" style="width:100%;height:100%;object-fit:cover;">
          @else
            {{ strtoupper(substr(auth()->user()?->nom ?? auth()->user()?->email ?? 'U', 0, 2)) }}
          @endif
        </div>
        <div class="nested-dropdown" id="userDropdown"
             style="position:absolute;right:0;left:auto;top:calc(100% + 6px);min-width:220px;">
          <div style="padding:12px 14px;border-bottom:1px solid var(--gel-border);">
            <div style="font-weight:600;">{{ auth()->user()?->nom ?? 'Utilisateur' }}</div>
            <div style="font-size:12px;color:rgba(255,255,255,0.5);">{{ auth()->user()?->email }}</div>
            <div style="font-size:11px;color:var(--gel-primary);font-weight:500;margin-top:2px;">Client Entreprise</div>
          </div>
          <div class="dd-item" data-route="profile"><i class="fas fa-user me-2"></i> Mon profil</div>
          <div class="dd-item" data-route="settings"><i class="fas fa-cog me-2"></i> Paramètres</div>
          <div class="dd-divider"></div>
          <div class="dd-item" onclick="event.preventDefault();document.getElementById('logoutForm').submit();">
            <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
          </div>
        </div>
      </div>
    </div>
  </header>

  {{-- ════════════════════════════════════════════ SIDEBAR ═══════════════ --}}
  <aside class="gel-sidebar">
    <div style="padding: 16px 16px 8px;">
      <button class="btn-nouveau-sidebar" onclick="toggleDropdown('dropdownNouveauSidebar')">
        <i class="fas fa-plus"></i> Nouveau
      </button>
      <div class="mega-menu" id="dropdownNouveauSidebar" style="min-width: 250px;">
        <div class="mega-col">
          <div class="mega-header">ACTIONS RAPIDES</div>
          <a href="{{ route('gel-client.ventes.factures.create') }}" class="mega-item"><i class="fas fa-file-invoice mega-icon"></i> Nouvelle facture</a>
          <a href="{{ route('gel-client.achats.depenses.create') }}" class="mega-item"><i class="fas fa-receipt mega-icon"></i> Soumettre une dépense</a>
          <a href="{{ route('gel-client.documents.index') }}" class="mega-item"><i class="fas fa-upload mega-icon"></i> Envoyer un document</a>
          <a href="{{ route('gel-client.coordination.index') }}" class="mega-item"><i class="fas fa-comment-dots mega-icon"></i> Message au cabinet</a>
        </div>
      </div>
    </div>

    <ul class="sidebar-menu">
      <div class="sidebar-section">Général</div>
      <li class="sidebar-item {{ (isset($currentSection) && $currentSection == 'dashboard') || request()->routeIs('gel-client.dashboard') || request()->routeIs('gel-client.home') ? 'active' : '' }}" onclick="window.location.href='{{ route('gel-client.dashboard') }}'"><i class="fas fa-tachometer-alt me-2"></i> Tableau de bord</li>
      <li class="sidebar-item {{ (isset($currentSection) && $currentSection == 'coordination') || request()->routeIs('gel-client.coordination*') ? 'active' : '' }}" onclick="window.location.href='{{ route('gel-client.coordination.index') }}'"><i class="fas fa-comments me-2"></i> Messagerie Cabinet</li>

      <div class="sidebar-section">Mes Opérations</div>
      <li class="sidebar-item {{ (isset($currentSection) && $currentSection == 'ventes') || request()->routeIs('gel-client.ventes*') ? 'active' : '' }}" onclick="window.location.href='{{ route('gel-client.ventes.factures.index') }}'"><i class="fas fa-file-invoice-dollar me-2"></i> Mes Ventes / Factures</li>
      <li class="sidebar-item {{ (isset($currentSection) && $currentSection == 'achats') || request()->routeIs('gel-client.achats*') ? 'active' : '' }}" onclick="window.location.href='{{ route('gel-client.achats.depenses.index') }}'"><i class="fas fa-receipt me-2"></i> Mes Dépenses (Justificatifs)</li>
      
      <div class="sidebar-section">Documents</div>
      <li class="sidebar-item {{ (isset($currentSection) && $currentSection == 'documents') || request()->routeIs('gel-client.documents*') ? 'active' : '' }}" onclick="window.location.href='{{ route('gel-client.documents.index') }}'"><i class="fas fa-folder-open me-2"></i> Boîte à documents</li>
    </ul>
 
    {{-- User Footer --}}
    @php $u = auth()->user(); @endphp
    @if($u)
    <div class="sidebar-footer" style="padding:12px; border-top: 1px solid var(--gel-border); display:flex; align-items:center; gap:10px; margin-top:auto;">
      <div class="avatar" style="width:32px;height:32px;font-size:11px;overflow:hidden;display:flex;align-items:center;justify-content:center;background:var(--gel-primary);color:white;">
        @if($u->photo)
          <img src="{{ asset('storage/' . $u->photo) }}" style="width:100%;height:100%;object-fit:cover;">
        @else
          {{ strtoupper(substr($u->name ?? $u->email, 0, 2)) }}
        @endif
      </div>
      <div style="flex:1;min-width:0;">
        <div style="font-size:13px;font-weight:600;color:var(--gel-text-primary);">{{ $u->name ?? $u->email }}</div>
        <div style="font-size:11px;color:rgba(255,255,255,0.5);">Espace Entreprise</div>
      </div>
      <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logoutForm').submit();" style="color:rgba(255,255,255,0.5);font-size:16px;">
        <i class="fas fa-sign-out-alt"></i>
      </a>
    </div>
    @endif
  </aside>



  {{-- ════════════════════════════════════════════ CONTENT ═══════════════ --}}
  <main class="gel-content">

    {{-- Flash messages --}}
    @if(session('success'))
    <div style="display:none;" id="gel-flash-message" data-message="{{ session('success') }}" data-type="success"></div>
    @endif
    @if(session('error'))
    <div style="display:none;" id="gel-flash-message" data-message="{{ session('error') }}" data-type="error"></div>
    @endif

    @yield('content')
  </main>

  {{-- ════════════════════════════════════════════ TOAST CONTAINER ═══════ --}}
  <div class="gel-toast-container" id="gelToastContainer"></div>

  {{-- ════════════════════════════════════════════ OVERLAY + SLIDE PANEL ═══ --}}
  <div class="panel-overlay" id="panelOverlay" onclick="closeSlidePanel()"></div>
  <div class="slide-panel" id="slidePanel">
    <div class="panel-header">
      <h3 id="panelTitle">Panel</h3>
      <button class="panel-close" onclick="closeSlidePanel()">✕</button>
    </div>
    <div class="panel-body" id="panelBody">
      <p style="color:var(--gel-text-secondary);">Contenu du panel...</p>
    </div>
  </div>

  {{-- Logout form --}}
  <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
  
  {{-- Formulaire de changement de contexte --}}
  <form id="switchContextForm" method="POST" action="{{ url('/context/switch') }}" style="display:none;">
    @csrf
    <input type="hidden" name="client_id" id="switchContextClientId">
  </form>

  {{-- ════════════════════════════════════════════ JAVASCRIPT ════════════ --}}
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      'use strict';

      var activeDropdown = null;
      var closeTimer = null;

      // ═══════════════════════════════════════════════
      // POSITIONNEMENT DYNAMIQUE DES DROPDOWNS
      // ═══════════════════════════════════════════════

      function positionDropdown(ddId, triggerEl) {
        var dd = document.getElementById(ddId);
        var trigger = triggerEl;
        if (!dd || !trigger) return;

        var triggerRect = trigger.getBoundingClientRect();
        var ddWidth = dd.offsetWidth || 230;

        var top = triggerRect.top;
        var ddHeight = dd.offsetHeight || 300;
        if (top + ddHeight > window.innerHeight - 20) {
          top = Math.max(10, window.innerHeight - ddHeight - 20);
        }

        var parentDropdown = trigger.closest('.nested-dropdown');
        if (parentDropdown) {
          dd.style.left = (triggerRect.right - 4) + 'px';
        } else {
          dd.style.left = (triggerRect.right - 4) + 'px';
        }

        dd.style.top = top + 'px';
      }

      // ═══════════════════════════════════════════════
      // OUVERTURE / FERMETURE
      // ═══════════════════════════════════════════════

      function openDropdown(id, trigger) {
        var dd = document.getElementById(id);
        if (!dd) return;

        document.querySelectorAll('.nested-dropdown.open').forEach(function(el) {
          if (el.id !== id && !el.contains(trigger)) {
            el.classList.remove('open');
          }
        });

        positionDropdown(id, trigger);
        dd.classList.add('open');
        activeDropdown = id;
      }

      function closeDropdown(id) {
        var dd = document.getElementById(id);
        if (dd) {
          dd.classList.remove('open');
          dd.querySelectorAll('.nested-dropdown.open').forEach(function(child) {
            child.classList.remove('open');
          });
        }
        if (activeDropdown === id) activeDropdown = null;
      }

      window.closeAllDropdowns = function() {
        document.querySelectorAll('.nested-dropdown.open').forEach(function(el) {
          el.classList.remove('open');
        });
        activeDropdown = null;
      };

      // ═══════════════════════════════════════════════
      // SURVOL SIDEBAR → OUVERTURE DROPDOWN
      // ═══════════════════════════════════════════════

      document.querySelectorAll('.sidebar-item.has-children').forEach(function(item) {
        var ddId = item.dataset.dropdown;
        var dd = document.getElementById(ddId);
        if (!dd) return;

        item.addEventListener('mouseenter', function() {
          clearTimeout(closeTimer);
          openDropdown(ddId, this);
        });

        item.addEventListener('mouseleave', function() {
          var ddEl = document.getElementById(ddId);
          closeTimer = setTimeout(function() {
            if (ddEl && !ddEl.matches(':hover') && !ddEl.querySelector(':hover')) {
              closeDropdown(ddId);
            }
          }, 400);
        });
      });

      // ═══════════════════════════════════════════════
      // SURVOL DROPDOWN → MAINTIEN OUVERT
      // ═══════════════════════════════════════════════

      document.querySelectorAll('.nested-dropdown').forEach(function(dd) {
        dd.addEventListener('mouseenter', function() {
          clearTimeout(closeTimer);
        });

        dd.addEventListener('mouseleave', function() {
          var self = this;
          closeTimer = setTimeout(function() {
            self.classList.remove('open');
            self.querySelectorAll('.nested-dropdown.open').forEach(function(child) {
              child.classList.remove('open');
            });
          }, 400);
        });
      });

      // ═══════════════════════════════════════════════
      // SURVOL ITEM AVEC ENFANTS (NIVEAU 3)
      // ═══════════════════════════════════════════════

      document.querySelectorAll('.dd-item.has-children').forEach(function(item) {
        var ddId = item.dataset.dropdown;
        var dd = document.getElementById(ddId);
        if (!dd) return;

        item.addEventListener('mouseenter', function(e) {
          e.stopPropagation();
          clearTimeout(closeTimer);
          positionDropdown(ddId, this);
          dd.classList.add('open');
        });

        item.addEventListener('mouseleave', function() {
          var ddEl = document.getElementById(ddId);
          closeTimer = setTimeout(function() {
            if (ddEl && !ddEl.matches(':hover') && !ddEl.querySelector(':hover')) {
              ddEl.classList.remove('open');
            }
          }, 400);
        });
      });

      // ═══════════════════════════════════════════════
      // CLIC SUR ÉLÉMENT AVEC DATA-PAGE
      // ═══════════════════════════════════════════════

      document.querySelectorAll('[data-page]').forEach(function(item) {
        item.addEventListener('click', function(e) {
          e.stopPropagation();
          var page = this.dataset.page;

          // Activer l'élément cliqué
          document.querySelectorAll('.dd-item.active, .sidebar-item.active').forEach(function(el) {
            el.classList.remove('active');
          });
          this.classList.add('active');

          // Si c'est un dd-item dans un dropdown, activer aussi le parent sidebar
          var parentSidebar = this.closest('.sidebar-item');
          if (parentSidebar) {
            parentSidebar.classList.add('active');
          }

          closeAllDropdowns();
          navigateTo(page);
        });
      });

      // ═══════════════════════════════════════════════
      // FONCTION DE NAVIGATION
      // ═══════════════════════════════════════════════

      window.navigateTo = function(page) {
        if (page && page.indexOf('client-') === 0) {
          var clientId = page.replace('client-', '');
          window.open("'#'?client_id=" + clientId, '_blank');
          return;
        }

        var titles = {
          'dashboard': '📊 Tableau de bord',
          'clients': '📋 Mes clients',
          'plan-comptable': '📋 Plan comptable SYSCOHADA',
          'ecritures': '📝 Saisie d\'écritures',
          'grand-livre': '📊 Grand livre',
          'balance': '⚖️ Balance',
          'bilan': '📋 Bilan (Actif/Passif)',
          'cr': '📊 Compte de résultat',
          'sig': '📈 SIG',
          'tafire': '📑 TAFIRE',
          'tresorerie': '💰 Flux de trésorerie',
          'taches': '✅ Tâches',
          'workflows': '🤖 Workflows',
          'journaux': '📋 Journaux',
          'factures': '📄 Factures',
          'devis': '📝 Devis',
          'produits': '📦 Produits & services',
          'paiements': '💳 Paiements reçus',
          'factures-recurrentes': '🔄 Factures récurrentes',
          'relances': '📨 Relances',
          'depenses-achats': '💰 Dépenses & Achats',
          'bons-commande': '📋 Bons de commande',
          'fournisseurs': '🏭 Fournisseurs',
          'notes-frais': '🧾 Notes de frais',
          'transactions': '🏦 Transactions bancaires',
          'rapprochement': '🤝 Rapprochement bancaire',
          'regles-bancaires': '📋 Règles bancaires',
          'comptes-bancaires': '🏦 Comptes bancaires',
          'virements': '↔️ Virements',
          'rapports-standards': '📊 Rapports standards',
          'centre-performance': '🎯 Centre de performance',
          'rapports-personnalises': '📐 Rapports personnalisés',
          'rapports-sauvegardes': '💾 Rapports sauvegardés',
          'equipe': '👥 Équipe',
          'invitations': '✉️ Invitations',
          'settings': '⚙️ Paramètres',
          'profile': '👤 Mon profil'
        };

        var title = titles[page] || '📄 ' + page;

        // Redirection vers la vraie route si elle existe
        var routeMap = {
          // ─── Général ───
          'dashboard':                 '{{ route("gel-client.dashboard") }}',
          'coordination':              '{{ route("gel-client.coordination.index") }}',

          // ─── Opérations ───
          'factures':                  '{{ route("gel-client.ventes.factures.index") }}',
          'depenses':                  '{{ route("gel-client.achats.depenses.index") }}',

          // ─── Documents ───
          'documents':                 '{{ route("gel-client.documents.index") }}',
        };

        if (routeMap[page]) {
          window.location.href = routeMap[page];
          return;
        }

        // Fallback: contenu simulé
        var content = document.getElementById('content-area');
        if (content) {
          content.innerHTML = `
        <div class="gel-toast-icon-wrapper">
          ${icons[type] || icons['success']}
        </div>
        <div class="gel-toast-content">
          <div class="gel-toast-message">${message}</div>
        </div>
        <div class="gel-toast-progress"></div>
      `;
        }
      };

      // ═══════════════════════════════════════════════
      // CLIC EN DEHORS → FERME TOUT
      // ═══════════════════════════════════════════════

      document.addEventListener('click', function(e) {
        if (!e.target.closest('.sidebar-menu') &&
            !e.target.closest('.nested-dropdown') &&
            !e.target.closest('.topbar-btn') &&
            !e.target.closest('.btn-go-business') &&
            !e.target.closest('.avatar') &&
            !e.target.closest('.search-bar')) {
          closeAllDropdowns();
        }
      });

      // ═══════════════════════════════════════════════
      // ÉCHAP → FERME TOUT
      // ═══════════════════════════════════════════════

      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          closeAllDropdowns();
          closeSlidePanel();
        }
        if (e.key === 'k' && (e.ctrlKey || e.metaKey)) {
          e.preventDefault();
          var searchInput = document.getElementById('globalSearch');
          if (searchInput) searchInput.focus();
        }
      });

      // ═══════════════════════════════════════════════
      // TOAST SYSTEM
      // ═══════════════════════════════════════════════

      window.showToast = function(message, type) {
        type = type || 'success';
        var container = document.getElementById('gelToastContainer');
        if (!container) return;

        var icons = {
          'success': '<i class="bi bi-check-circle-fill"></i>',
          'error': '<i class="bi bi-x-circle-fill"></i>',
          'warning': '<i class="bi bi-exclamation-triangle-fill"></i>',
          'info': '<i class="bi bi-info-circle-fill"></i>'
        };

        var toast = document.createElement('div');
        toast.className = 'gel-toast' + (type === 'error' ? ' gel-toast-error' : type === 'warning' ? ' gel-toast-warning' : type === 'info' ? ' gel-toast-info' : '');
        
        t.innerHTML = `
        <div class="gel-toast-icon-wrapper">
          ${icons[type] || icons['success']}
        </div>
        <div class="gel-toast-content">
          <div class="gel-toast-message">${message}</div>
        </div>
        <div class="gel-toast-progress"></div>
      `;

        container.appendChild(toast);

        setTimeout(function() {
          if (toast && toast.parentNode) {
            toast.classList.add('exit');
            setTimeout(function() { if (toast && toast.parentNode) toast.remove(); }, 250);
          }
        }, 1500);
      };

      // ═══════════════════════════════════════════════
      // SLIDE PANEL
      // ═══════════════════════════════════════════════

      window.openSlidePanel = function(title) {
        var panel = document.getElementById('slidePanel');
        var overlay = document.getElementById('panelOverlay');
        var titleEl = document.getElementById('panelTitle');
        if (titleEl && title) titleEl.textContent = title;
        if (panel) panel.classList.add('open');
        if (overlay) overlay.classList.add('open');
      };

      window.closeSlidePanel = function() {
        var panel = document.getElementById('slidePanel');
        var overlay = document.getElementById('panelOverlay');
        if (panel) panel.classList.remove('open');
        if (overlay) overlay.classList.remove('open');
      };

      // ═══════════════════════════════════════════════
      // TOGGLE DROPDOWN (topbar: clic)
      // ═══════════════════════════════════════════════

      window.toggleDropdown = function(id) {
        var dd = document.getElementById(id);
        if (!dd) return;

        var isOpen = dd.classList.contains('open');
        document.querySelectorAll('.nested-dropdown.open').forEach(function(el) {
          if (el.id !== id) {
            el.classList.remove('open');
          }
        });

        if (isOpen) {
          dd.classList.remove('open');
        } else {
          dd.classList.add('open');
          activeDropdown = id;
        }
      };

      // ═══════════════════════════════════════════════
      // RECHERCHE
      // ═══════════════════════════════════════════════

      window.openSearchResults = function() {
        var dd = document.getElementById('searchResults');
        if (dd) dd.classList.add('open');
      };

      window.closeSearchResults = function() {
        var dd = document.getElementById('searchResults');
        if (dd) dd.classList.remove('open');
      };

      // ═══════════════════════════════════════════════
      // FLASH MESSAGE → AUTO TOAST
      // ═══════════════════════════════════════════════

      var flashMsg = document.getElementById('gel-flash-message');
      if (flashMsg) {
        showToast(flashMsg.dataset.message, flashMsg.dataset.type || 'success');
      }

      // Notifications interactives
      window.dismissNotification = function(el) {
        el.style.transition = 'opacity 200ms';
        el.style.opacity = '0';
        setTimeout(function() {
          el.remove();
          var badge = document.getElementById('notifBadge');
          if (badge) {
            var count = parseInt(badge.textContent) - 1;
            if (count > 0) {
              badge.textContent = count;
            } else {
              badge.remove();
              document.getElementById('notifDropdown').querySelector('.dd-item').outerHTML = '<div class="dd-item" style="justify-content:center;color:rgba(255,255,255,0.5);">Aucune notification</div>';
            }
          }
        }, 200);
      };

      window.clearAllNotifications = function() {
        document.querySelectorAll('.notif-item').forEach(function(el) {
          el.remove();
        });
        var badge = document.getElementById('notifBadge');
        if (badge) badge.remove();
        showToast('Toutes les notifications ont été marquées comme lues', 'success');
      };

      // Fil d'actualité
      window.openActivityFeed = function() {
        var contentHtml = `
          <div style="display:flex; flex-direction:column; gap:16px;">
            <div style="border-bottom: 1px solid var(--gel-border); padding-bottom:12px;">
              <div style="font-weight:600; font-size:14px; color:var(--gel-text-primary);">Achat fournitures de bureau</div>
              <div style="font-size:12px; color:rgba(255,255,255,0.5);">Par Alice • Il y a 2h • Client: TechInnov</div>
              <div style="font-size:13px; margin-top:6px;">Écriture OD-2026-124 enregistrée pour 45 000 FCFA.</div>
            </div>
            <div style="border-bottom: 1px solid var(--gel-border); padding-bottom:12px;">
              <div style="font-weight:600; font-size:14px; color:var(--gel-text-primary);">Clôture période fiscale</div>
              <div style="font-size:12px; color:rgba(255,255,255,0.5);">Système • Il y a 1 jour • Cabinet</div>
              <div style="font-size:13px; margin-top:6px;">La déclaration de TVA du mois précédent a été générée.</div>
            </div>
            <div style="padding-bottom:12px;">
              <div style="font-weight:600; font-size:14px; color:var(--gel-text-primary);">Nouveau client ajouté</div>
              <div style="font-size:12px; color:rgba(255,255,255,0.5);">Par Alice • Il y a 3 jours</div>
              <div style="font-size:13px; margin-top:6px;">SARL AgroPro a été rattaché au cabinet comptable.</div>
            </div>
          </div>
        `;
        openSlidePanel('Fil d\'actualité');
        document.getElementById('panelBody').innerHTML = contentHtml;
      };

      // Panneau d'aide principal
      window.openHelpPanel = function() {
        var contentHtml = `
          <div style="display:flex; flex-direction:column; gap:16px;">
            <p style="font-size:14px; line-height:1.6;">Bienvenue dans le centre d'aide de <strong>GEL Accountant</strong>. Voici les guides et supports disponibles :</p>
            <button onclick="openGuideSaisie()" class="gel-btn gel-btn-secondary" style="justify-content:flex-start; width:100%; text-align:left;"><i class="fas fa-book me-2"></i> Guide de saisie comptable</button>
            <button onclick="openTutoVideo()" class="gel-btn gel-btn-secondary" style="justify-content:flex-start; width:100%; text-align:left;"><i class="fas fa-video me-2"></i> Tutoriel vidéo d'onboarding</button>
            <button onclick="openSupportForm()" class="gel-btn gel-btn-secondary" style="justify-content:flex-start; width:100%; text-align:left;"><i class="fas fa-envelope me-2"></i> Contacter le support technique</button>
            <div style="margin-top:20px; padding:14px; background:var(--gel-sidebar-bg); border-radius:8px; font-size:12px; color:var(--gel-text-secondary); border:1px solid var(--gel-border);">
              <strong>Version de l'application :</strong> v2.5.0<br>
              <strong>Normes comptables :</strong> SYSCOHADA Révisé<br>
              <strong>Cabinet rattaché :</strong> GEL Cabinet
            </div>
          </div>
        `;
        openSlidePanel('Centre d\'aide & Documentation');
        document.getElementById('panelBody').innerHTML = contentHtml;
      };

      // 1. Guide de saisie comptable complet
      window.openGuideSaisie = function() {
        var contentHtml = `
          <button onclick="openHelpPanel()" class="gel-btn gel-btn-secondary gel-btn-sm mb-3"><i class="fas fa-arrow-left"></i> Retour aux ressources</button>
          <div style="font-size:13px; line-height:1.6; color:var(--gel-text-primary);">
            <h3 style="font-size:16px; font-weight:700; color:var(--gel-primary); margin-bottom:12px;"><i class="fas fa-book"></i> Guide Pratique de Saisie Comptable (SYSCOHADA)</h3>
            <p style="color:var(--gel-text-secondary); margin-bottom:16px;">Ce guide synthétise les règles d'enregistrement des écritures courantes du cabinet comptable.</p>
            
            <div class="card p-3 mb-3 border-0" style="background:#f8fafc; border-left:4px solid #2CA01C !important;">
              <h4 style="font-size:14px; font-weight:700; margin-bottom:6px;">1. Factures de Vente (Classe 7)</h4>
              <p style="margin-bottom:8px; font-size:12px;">Enregistrement d'une facture émise aux clients :</p>
              <ul style="margin-bottom:8px; padding-left:18px; font-size:12px;">
                <li><strong>Débit : 411000 - Clients</strong> (Montant TTC)</li>
                <li><strong>Crédit : 701000 - Ventes de marchandises</strong> (Montant HT)</li>
                <li><strong>Crédit : 443100 - TVA facturée sur ventes</strong> (Montant TVA 18%)</li>
              </ul>
              <div style="font-size:11px; background:#ffffff; padding:8px; border-radius:4px; border:1px solid #e2e8f0;">
                <em>Exemple : Vente de 500 000 F HT (+ 90 000 F TVA = 590 000 F TTC)</em><br>
                • D. 411000 : 590 000 | C. 701000 : 500 000 | C. 443100 : 90 000
              </div>
            </div>

            <div class="card p-3 mb-3 border-0" style="background:#f8fafc; border-left:4px solid #0284c7 !important;">
              <h4 style="font-size:14px; font-weight:700; margin-bottom:6px;">2. Achats & Charges Externes (Classe 6)</h4>
              <p style="margin-bottom:8px; font-size:12px;">Comptabilisation des factures fournisseurs :</p>
              <ul style="margin-bottom:8px; padding-left:18px; font-size:12px;">
                <li><strong>Débit : 601000 - Achats de marchandises</strong> (HT)</li>
                <li><strong>Débit : 445200 - TVA récupérable sur achats</strong> (TVA)</li>
                <li><strong>Crédit : 401000 - Fournisseurs</strong> (TTC)</li>
              </ul>
            </div>

            <div class="card p-3 mb-3 border-0" style="background:#f8fafc; border-left:4px solid #f59e0b !important;">
              <h4 style="font-size:14px; font-weight:700; margin-bottom:6px;">3. Trésorerie & Rapprochement Bancaire (Classe 5)</h4>
              <ul style="margin-bottom:0; padding-left:18px; font-size:12px;">
                <li><strong>Encaissement :</strong> Débit 521000 (Banque) / Crédit 411000 (Client)</li>
                <li><strong>Décaissement :</strong> Débit 401000 (Fournisseur) / Crédit 521000 (Banque)</li>
              </ul>
            </div>

            <div class="card p-3 mb-3 border-0" style="background:#f8fafc; border-left:4px solid #64748b !important;">
              <h4 style="font-size:14px; font-weight:700; margin-bottom:6px;">4. Amortissements d'Exercice (Classe 68 & 28)</h4>
              <ul style="margin-bottom:0; padding-left:18px; font-size:12px;">
                <li><strong>Débit : 681300 - Dotations aux amortissements</strong></li>
                <li><strong>Crédit : 281500 - Amortissements du matériel</strong></li>
              </ul>
            </div>

            <button class="gel-btn gel-btn-primary w-100 mt-2" onclick="showToast('Téléchargement du Guide PDF SYSCOHADA démarré...', 'info')">
              <i class="fas fa-file-pdf me-1"></i> Télécharger le guide complet (PDF)
            </button>
          </div>
        `;
        openSlidePanel('Guide de Saisie Comptable');
        document.getElementById('panelBody').innerHTML = contentHtml;
      };

      // 2. Tutoriel vidéo d'onboarding
      window.openTutoVideo = function() {
        var contentHtml = `
          <button onclick="openHelpPanel()" class="gel-btn gel-btn-secondary gel-btn-sm mb-3"><i class="fas fa-arrow-left"></i> Retour aux ressources</button>
          <div style="font-size:13px; line-height:1.6; color:var(--gel-text-primary);">
            <h3 style="font-size:16px; font-weight:700; color:var(--gel-primary); margin-bottom:12px;"><i class="fas fa-video"></i> Formation & Tutoriel Vidéo</h3>
            
            <div style="position:relative; width:100%; height:190px; background:#0f172a; border-radius:8px; display:flex; flex-direction:column; align-items:center; justify-content:center; color:white; margin-bottom:16px; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
              <div style="width:56px; height:56px; border-radius:50%; background:var(--gel-primary); color:white; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:20px; transition:transform 0.2s;" onclick="showToast('Lecture du vidéo tutoriel de formation...', 'info')">
                <i class="fas fa-play" style="margin-left:4px;"></i>
              </div>
              <div style="margin-top:12px; font-size:13px; font-weight:600;">GEL Accountant - Guide vidéo pas à pas (09:45)</div>
              <div style="font-size:11px; color:#94a3b8; margin-top:2px;">Format de formation comptable enregistrée</div>
            </div>

            <h4 style="font-size:14px; font-weight:700; margin-bottom:10px;">Chapitres de la formation :</h4>
            <div class="list-group mb-3">
              <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" style="cursor:pointer;" onclick="showToast('Chapitre 1 : Prise en main (00:00)', 'info')">
                <div><strong>1. Aperçu du Tableau de bord & navigation</strong><br><small class="text-muted">Découverte de l'interface du cabinet</small></div>
                <span class="badge bg-secondary">00:00</span>
              </div>
              <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" style="cursor:pointer;" onclick="showToast('Chapitre 2 : Gestion des clients (02:15)', 'info')">
                <div><strong>2. Gestion des clients & dossiers</strong><br><small class="text-muted">Configuration des entreprises rattachées</small></div>
                <span class="badge bg-secondary">02:15</span>
              </div>
              <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" style="cursor:pointer;" onclick="showToast('Chapitre 3 : Saisie d\'écritures (05:40)', 'info')">
                <div><strong>3. Saisie d'écritures & pièces</strong><br><small class="text-muted">Journaux, lettrage et validation</small></div>
                <span class="badge bg-secondary">05:40</span>
              </div>
              <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" style="cursor:pointer;" onclick="showToast('Chapitre 4 : États financiers (08:10)', 'info')">
                <div><strong>4. Génération des Bilan, CR & Liasses</strong><br><small class="text-muted">Édition des états SYSCOHADA</small></div>
                <span class="badge bg-secondary">08:10</span>
              </div>
            </div>
          </div>
        `;
        openSlidePanel('Tutoriel Vidéo d\'Onboarding');
        document.getElementById('panelBody').innerHTML = contentHtml;
      };

      // 3. Formulaire de support technique
      window.openSupportForm = function() {
        var contentHtml = `
          <button onclick="openHelpPanel()" class="gel-btn gel-btn-secondary gel-btn-sm mb-3"><i class="fas fa-arrow-left"></i> Retour aux ressources</button>
          <div style="font-size:13px; line-height:1.6; color:var(--gel-text-primary);">
            <h3 style="font-size:16px; font-weight:700; color:var(--gel-primary); margin-bottom:8px;"><i class="fas fa-headset me-1"></i> Contacter le Support Technique</h3>
            <p style="color:var(--gel-text-secondary); margin-bottom:16px;">Posez vos questions ou signalez un problème. Notre équipe vous répondra sous 24h.</p>

            <form onsubmit="event.preventDefault(); submitSupportTicket(this);">
              <div class="gel-form-group">
                <label>Objet de votre demande *</label>
                <select name="subject" class="gel-form-select" required>
                  <option value="Saisie comptable">Assistance sur la saisie d'écritures</option>
                  <option value="États financiers">Génération des Bilan, CR ou Liasses</option>
                  <option value="Permissions clients">Accès clients / Contexte entreprise</option>
                  <option value="Anomalie technique">Signaler un problème d'affichage ou bug</option>
                  <option value="Autre">Autre demande</option>
                </select>
              </div>
              <div class="gel-form-group">
                <label>Priorité</label>
                <select name="priority" class="gel-form-select">
                  <option value="Normale" selected>Normale</option>
                  <option value="Urgente">Urgente (Période fiscale / Bloquant)</option>
                  <option value="Basse">Basse (Suggestion / Question)</option>
                </select>
              </div>
              <div class="gel-form-group">
                <label>Message / Détails *</label>
                <textarea name="description" class="gel-form-control" rows="4" required placeholder="Décrivez votre demande avec le plus de précisions possible..."></textarea>
              </div>
              <div class="gel-form-group">
                <label>Pièce jointe (capture ou document)</label>
                <input type="file" name="attachment" class="gel-form-control">
              </div>
              <button type="submit" class="gel-btn gel-btn-primary w-100 mt-3">
                <i class="fas fa-paper-plane me-1"></i> Envoyer le ticket de support
              </button>
            </form>
          </div>
        `;
        openSlidePanel('Support Technique');
        document.getElementById('panelBody').innerHTML = contentHtml;
      };

      // Soumission du ticket de support
      window.submitSupportTicket = function(form) {
        var num = Math.floor(1000 + Math.random() * 9000);
        showToast('Ticket de support #SUP-' + num + ' transmis avec succès !', 'success');
        openHelpPanel();
      };
    });
  </script>

  {{-- ════════════════════════════════════════════════════════════════════ --}}
  {{-- RECHERCHE GLOBALE TEMPS RÉEL                                      --}}
  {{-- ════════════════════════════════════════════════════════════════════ --}}
  <script>
    (function() {
      var searchTimer = null;
      var searchUrl = ''#'';

      window.handleGlobalSearch = function(value) {
        var q = (value || '').trim();
        var resultsEl = document.getElementById('searchResults');
        var defaultEl = document.getElementById('searchDefaultContent');
        var ajaxEl = document.getElementById('searchAjaxResults');
        var loaderEl = document.getElementById('searchLoader');

        // Ouvrir le dropdown
        if (resultsEl) resultsEl.classList.add('open');

        if (q.length < 2) {
          // Afficher les raccourcis par défaut
          if (defaultEl) defaultEl.style.display = 'block';
          if (ajaxEl) { ajaxEl.style.display = 'none'; ajaxEl.innerHTML = ''; }
          if (loaderEl) loaderEl.style.display = 'none';
          return;
        }

        // Masquer les raccourcis, afficher le loader
        if (defaultEl) defaultEl.style.display = 'none';
        if (loaderEl) loaderEl.style.display = 'block';
        if (ajaxEl) ajaxEl.style.display = 'none';

        // Debounce 300ms
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
          fetch(searchUrl + '?q=' + encodeURIComponent(q), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
          })
          .then(function(res) { return res.json(); })
          .then(function(data) {
            if (loaderEl) loaderEl.style.display = 'none';

            var html = '';
            if (data.results && data.results.length > 0) {
              data.results.forEach(function(group) {
                html += '<div class="dd-header">' + escapeHtml(group.category).toUpperCase() + '</div>';
                group.items.forEach(function(item) {
                  var badgeHtml = '';
                  if (item.badge) {
                    var cls = item.badge_class || 'secondary';
                    badgeHtml = '<span class="gel-badge gel-badge-' + cls + '" style="font-size:10px;margin-left:auto;">' + escapeHtml(item.badge) + '</span>';
                  }
                  html += '<a href="' + escapeHtml(item.url) + '" class="dd-item" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:8px;">';
                  html += '<i class="' + escapeHtml(item.icon) + '" style="width:16px;color:var(--gel-primary);"></i>';
                  html += '<div style="flex:1;min-width:0;">';
                  html += '<div style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + escapeHtml(item.title) + '</div>';
                  if (item.subtitle) {
                    html += '<div style="font-size:11px;color:rgba(255,255,255,0.5);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + escapeHtml(item.subtitle) + '</div>';
                  }
                  html += '</div>';
                  html += badgeHtml;
                  html += '</a>';
                });
              });
            } else {
              html = '<div style="padding:24px;text-align:center;color:rgba(255,255,255,0.5);">';
              html += '<i class="fas fa-search" style="font-size:24px;margin-bottom:8px;display:block;opacity:0.3;"></i>';
              html += 'Aucun résultat pour « <strong>' + escapeHtml(q) + '</strong> »</div>';
            }

            if (ajaxEl) {
              ajaxEl.innerHTML = html;
              ajaxEl.style.display = 'block';
            }
          })
          .catch(function(err) {
            if (loaderEl) loaderEl.style.display = 'none';
            if (ajaxEl) {
              ajaxEl.innerHTML = '<div style="padding:16px;text-align:center;color:var(--gel-danger);">Erreur de recherche</div>';
              ajaxEl.style.display = 'block';
            }
          });
        }, 300);
      };

      window.openSearchResults = function() {
        var el = document.getElementById('searchResults');
        if (el) el.classList.add('open');
      };

      window.closeSearchResults = function() {
        var el = document.getElementById('searchResults');
        if (el) el.classList.remove('open');
      };

      function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
      }
    })();

    // ═══════════════════════════════════════════════
    // NOTIFICATIONS — Dismiss & Clear
    // ═══════════════════════════════════════════════

    window.dismissNotification = function(el) {
      if (el) {
        el.style.transition = 'opacity 0.2s, max-height 0.3s';
        el.style.opacity = '0';
        el.style.maxHeight = '0';
        el.style.overflow = 'hidden';
        setTimeout(function() { el.remove(); updateNotifBadge(); }, 300);
      }
    };

    window.clearAllNotifications = function() {
      var dropdown = document.getElementById('notifDropdown');
      if (!dropdown) return;
      dropdown.querySelectorAll('.notif-item').forEach(function(el) {
        el.remove();
      });
      // Ajouter un message "vide"
      var divider = dropdown.querySelector('.dd-divider');
      if (divider) divider.remove();
      var voirToutes = dropdown.querySelector('.dd-item:last-child');
      if (voirToutes && voirToutes.textContent.includes('Voir toutes')) voirToutes.remove();

      var emptyDiv = document.createElement('div');
      emptyDiv.className = 'dd-item';
      emptyDiv.style.cssText = 'justify-content:center;color:rgba(255,255,255,0.5);padding:20px;';
      emptyDiv.innerHTML = '<i class="fas fa-inbox me-1"></i> Aucune notification';
      dropdown.appendChild(emptyDiv);

      updateNotifBadge();
      showToast('Notifications effacées', 'success');
    };

    function updateNotifBadge() {
      var badge = document.getElementById('notifBadge');
      var dropdown = document.getElementById('notifDropdown');
      if (!badge || !dropdown) return;
      var count = dropdown.querySelectorAll('.notif-item').length;
      if (count > 0) {
        badge.textContent = count;
        badge.style.display = '';
      } else {
        badge.style.display = 'none';
      }
    }

    // ═══════════════════════════════════════════════
    // GO TO BUSINESS — Client click handler
    // ═══════════════════════════════════════════════

    document.querySelectorAll('[data-route]').forEach(function(item) {
      item.addEventListener('click', function(e) {
        var route = this.dataset.route;
        if (!route) return;
        e.stopPropagation();

        if (route.indexOf('client-') === 0) {
          var clientId = route.replace('client-', '');
          window.open("{{ url('gel-accountant/client') }}/" + clientId + "/select", '_blank');
        } else {
          navigateTo(route);
        }
        closeAllDropdowns();
      });
    });
  </script>

  @stack('scripts')
  {{-- Global Side Panel --}}
  <div id="gelSidePanelOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:9998; backdrop-filter:blur(2px);" onclick="closePanel()"></div>
  <div id="gelSidePanel" style="position:fixed; top:0; right:-500px; width:450px; height:100%; background:white; z-index:9999; box-shadow:-5px 0 25px rgba(0,0,0,0.1); transition:right 0.3s ease; display:flex; flex-direction:column;">
    <div style="padding:20px; border-bottom:1px solid var(--gel-border); display:flex; justify-content:space-between; align-items:center; background:var(--gel-sidebar-bg); color:white;">
      <h3 id="gelSidePanelTitle" style="margin:0; font-size:18px; font-weight:600;">Titre</h3>
      <button onclick="closePanel()" style="background:none; border:none; color:white; font-size:24px; cursor:pointer;">&times;</button>
    </div>
    <div id="gelSidePanelBody" style="padding:20px; flex:1; overflow-y:auto;">
      {{-- Form content --}}
    </div>
    <div id="gelSidePanelFooter" style="padding:15px 20px; border-top:1px solid var(--gel-border); display:flex; justify-content:flex-end; gap:10px; background:#f8fafc;">
      {{-- Buttons --}}
    </div>
  </div>

  <script>
    function openPanel(title, bodyHtml, footerHtml) {
      document.getElementById('gelSidePanelTitle').innerText = title;
      document.getElementById('gelSidePanelBody').innerHTML = bodyHtml;
      document.getElementById('gelSidePanelFooter').innerHTML = footerHtml;
      
      document.getElementById('gelSidePanelOverlay').style.display = 'block';
      setTimeout(() => {
        document.getElementById('gelSidePanel').style.right = '0';
      }, 10);
    }

    function closePanel() {
      document.getElementById('gelSidePanel').style.right = '-500px';
      setTimeout(() => {
        document.getElementById('gelSidePanelOverlay').style.display = 'none';
      }, 300);
    }
  </script>

  {{-- S1.3 / S4.1 — Temps réel comptable : notifications + coordination secrétaire↔comptable --}}
  <script>
    (function () {
      @if(auth()->check())
        var userId = {{ auth()->id() }};
        var cabId = {{ auth()->user()->cabinet_id ?? 'null' }};
      @else
        var userId = null;
        var cabId = null;
      @endif

      if (userId && window.Echo && typeof window.Echo !== 'undefined') {

        // S1.3 — Notifications temps réel (canal privé user.{id})
        window.Echo.private('user.' + userId)
          .listen('.NotificationRecuEvent', function (e) {
            const item = e.notificationData || e;
            // Son
            try { new Audio('/audio/notification.wav').play().catch(function(){}); } catch (err) {}
            // Badge
            const badge = document.getElementById('notifBadge');
            if (badge) {
              let c = parseInt(badge.innerText) || 0;
              c++;
              badge.style.display = 'block';
              badge.innerText = c > 99 ? '99+' : c;
            }
            if (item && item.title && typeof showToast === 'function') {
              showToast(item.title + (item.description ? ' : ' + item.description : ''), 'info');
            }
          });

        // S4.1 — Activité de coordination : l'écoute est configurée dans les
        // vues de coordination (gel-accountant.coordination.* et gel-secretary.coordination.*)
        // qui connaissent le client_id précis → scoping par entreprise.

        // P1 — Messages internes (secrétaire/admin) → resynchronisation badge
        if (cabId) {
          window.Echo.private('chat.interne.' + cabId + '.' + userId)
            .listen('.MessageEnvoyeEvent', function () {
              try { new Audio('/audio/notification.wav').play().catch(function(){}); } catch (err) {}
              const badge = document.getElementById('notifBadge');
              if (badge) {
                let c = parseInt(badge.innerText) || 0;
                c++;
                badge.style.display = 'block';
                badge.innerText = c > 99 ? '99+' : c;
              }
            });
        }
      }
    })();
  </script>
</body>
</html>
