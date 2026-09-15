/**
 * SIMTAQ Export & Print Utilities
 * Digunakan untuk ekspor data CSV / Excel dan Cetak Dokumen Resmi Yayasan Al Mukhlisin
 */

/**
 * Ekspor array of objects ke file CSV / Excel kompatibel
 * @param {string} filename - Nama file yang akan diunduh (tanpa ekstensi)
 * @param {Array<{label: string, field: string|Function}>} columns - Definisi kolom
 * @param {Array<Object>} rows - Data baris
 */
export function exportToCsv(filename, columns, rows) {
  if (!rows || !rows.length) {
    alert('Tidak ada data yang dapat diekspor.');
    return;
  }

  // Header baris
  const headerRow = columns.map(col => `"${(col.label || '').replace(/"/g, '""')}"`).join(',');

  // Isi data
  const dataRows = rows.map(row => {
    return columns.map(col => {
      let val = '';
      if (typeof col.field === 'function') {
        val = col.field(row);
      } else if (typeof col.field === 'string') {
        val = row[col.field];
      }
      if (val === null || val === undefined) val = '';
      val = String(val).replace(/"/g, '""');
      return `"${val}"`;
    }).join(',');
  });

  const csvContent = '\uFEFF' + [headerRow, ...dataRows].join('\r\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');

  const nowStr = new Date().toISOString().slice(0, 10);
  link.setAttribute('href', url);
  link.setAttribute('download', `${filename}_${nowStr}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

/**
 * Cetak Laporan Formal dengan Kop Surat Yayasan Al Mukhlisin
 * Membuka jendela print browser / Save as PDF secara instan
 * @param {Object} options
 * @param {string} options.title - Judul Laporan (misal: "LAPORAN BUKU KAS INDUK YAYASAN")
 * @param {string} [options.subtitle] - Sub-judul (misal: "Periode: September 2026")
 * @param {Array<{label: string, value: string|number, color?: string}>} [options.stats] - Metrik ringkasan
 * @param {Array<{label: string, field: string|Function, align?: string}>} options.columns - Kolom tabel
 * @param {Array<Object>} options.rows - Data baris tabel
 * @param {Object} [options.signatories] - Data penandatangan
 */
export function printReport({
  title = 'LAPORAN SISTEM SIMTAQ',
  subtitle = '',
  stats = [],
  columns = [],
  rows = [],
  signatories = [
    { title: 'Mengetahui,', role: 'Ketua Yayasan Al Mukhlisin', name: 'Ust. H. Ahmad Dahlan, Lc.' },
    { title: 'Dibuat Oleh,', role: 'Bagian Administrasi & Keuangan', name: 'H. Muhammad Yusuf' }
  ]
}) {
  const currentDate = new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });

  // Render Stats HTML
  let statsHtml = '';
  if (stats && stats.length) {
    statsHtml = `
      <div class="stats-grid">
        ${stats.map(s => `
          <div class="stat-card">
            <div class="stat-label">${s.label}</div>
            <div class="stat-value" style="color: ${s.color || '#0D7C66'};">${s.value}</div>
          </div>
        `).join('')}
      </div>
    `;
  }

  // Render Table Header
  const tableHeaderHtml = columns.map(c => `
    <th style="text-align: ${c.align || 'left'};">${c.label}</th>
  `).join('');

  // Render Table Body
  const tableBodyHtml = rows.map((row, idx) => {
    const cells = columns.map(c => {
      let val = '';
      if (typeof c.field === 'function') {
        val = c.field(row);
      } else if (typeof c.field === 'string') {
        val = row[c.field];
      }
      if (val === null || val === undefined) val = '-';
      return `<td style="text-align: ${c.align || 'left'};">${val}</td>`;
    }).join('');

    return `<tr><td>${idx + 1}</td>${cells}</tr>`;
  }).join('');

  // Render Signatories
  const signHtml = `
    <div class="signatures-wrapper">
      ${signatories.map(sig => `
        <div class="sig-box">
          <div class="sig-title">${sig.title || 'Mengetahui,'}</div>
          <div class="sig-role">${sig.role}</div>
          <div class="sig-space"></div>
          <div class="sig-name">( ${sig.name} )</div>
        </div>
      `).join('')}
    </div>
  `;

  const printWindow = window.open('', '_blank');
  if (!printWindow) {
    alert('Jendela cetak diblokir oleh browser. Izinkan popup untuk mencetak laporan.');
    return;
  }

  printWindow.document.write(`
    <!DOCTYPE html>
    <html lang="id">
    <head>
      <meta charset="UTF-8">
      <title>${title} - SIMTAQ</title>
      <style>
        * {
          box-sizing: border-box;
          margin: 0;
          padding: 0;
          font-family: 'Inter', Arial, sans-serif;
          color: #111827;
        }
        body {
          padding: 24px 32px;
          background: #fff;
          font-size: 11pt;
        }
        .header-letterhead {
          display: flex;
          align-items: center;
          border-bottom: 3px double #0D7C66;
          padding-bottom: 12px;
          margin-bottom: 20px;
        }
        .kop-logo {
          width: 72px;
          height: 72px;
          object-fit: cover;
          border-radius: 50%;
          margin-right: 18px;
        }
        .kop-text {
          flex: 1;
        }
        .kop-title {
          font-size: 16pt;
          font-weight: 800;
          color: #0D7C66;
          letter-spacing: 0.5px;
          line-height: 1.2;
        }
        .kop-subtitle {
          font-size: 12pt;
          font-weight: 700;
          color: #1F2937;
          margin-top: 2px;
        }
        .kop-address {
          font-size: 8.5pt;
          color: #4B5563;
          margin-top: 4px;
          line-height: 1.35;
        }
        .report-title-box {
          text-align: center;
          margin-bottom: 18px;
        }
        .report-title {
          font-size: 14pt;
          font-weight: 800;
          text-transform: uppercase;
          color: #111827;
          letter-spacing: 0.5px;
        }
        .report-subtitle {
          font-size: 10pt;
          color: #4B5563;
          margin-top: 3px;
        }
        .report-meta {
          font-size: 8.5pt;
          color: #6B7280;
          margin-top: 2px;
        }
        .stats-grid {
          display: flex;
          gap: 12px;
          margin-bottom: 18px;
        }
        .stat-card {
          flex: 1;
          border: 1px solid #E5E7EB;
          border-radius: 6px;
          padding: 8px 12px;
          background: #F9FAFB;
        }
        .stat-label {
          font-size: 8.5pt;
          color: #6B7280;
          font-weight: 600;
          text-transform: uppercase;
        }
        .stat-value {
          font-size: 12pt;
          font-weight: 800;
          margin-top: 3px;
        }
        table {
          width: 100%;
          border-collapse: collapse;
          margin-bottom: 24px;
          font-size: 9pt;
        }
        th, td {
          border: 1px solid #D1D5DB;
          padding: 6px 8px;
        }
        th {
          background-color: #F3F4F6;
          font-weight: 700;
          color: #374151;
        }
        tr:nth-child(even) td {
          background-color: #FBFDFB;
        }
        .signatures-wrapper {
          display: flex;
          justify-content: space-between;
          margin-top: 32px;
          page-break-inside: avoid;
        }
        .sig-box {
          width: 240px;
          text-align: center;
        }
        .sig-title {
          font-size: 9pt;
          color: #4B5563;
        }
        .sig-role {
          font-size: 9.5pt;
          font-weight: 700;
          margin-top: 2px;
        }
        .sig-space {
          height: 65px;
        }
        .sig-name {
          font-size: 9.5pt;
          font-weight: 700;
          border-top: 1px solid #9CA3AF;
          padding-top: 4px;
        }
        @media print {
          body {
            padding: 10mm;
          }
          @page {
            size: auto;
            margin: 10mm;
          }
        }
      </style>
    </head>
    <body>
      <!-- KOP SURAT RESMI -->
      <div class="header-letterhead">
        <img src="/assets/logo-inti.jpeg" alt="Logo" class="kop-logo" onerror="this.style.display='none'">
        <div class="kop-text">
          <div class="kop-title">SIMTAQ - YAYASAN AL MUKHLISIN</div>
          <div class="kop-subtitle">Pondok Pesantren Tahfizul Qur'an Terpadu</div>
          <div class="kop-address">
            Jl. Raya Pondok Pesantren No. 12, Jawa Barat &bull; Telp/WA: 0812-3456-7890<br>
            Email: info@simtaq-almukhlisin.org &bull; Website: www.simtaq-almukhlisin.test
          </div>
        </div>
      </div>

      <!-- JUDUL LAPORAN -->
      <div class="report-title-box">
        <div class="report-title">${title}</div>
        ${subtitle ? `<div class="report-subtitle">${subtitle}</div>` : ''}
        <div class="report-meta">Dicetak pada: ${currentDate} &bull; Melalui Sistem Informasi SIMTAQ</div>
      </div>

      <!-- RINGKASAN METRIK (JIKA ADA) -->
      ${statsHtml}

      <!-- TABEL DATA -->
      <table>
        <thead>
          <tr>
            <th style="width: 35px; text-align: center;">No.</th>
            ${tableHeaderHtml}
          </tr>
        </thead>
        <tbody>
          ${tableBodyHtml}
        </tbody>
      </table>

      <!-- AREA TANDA TANGAN -->
      ${signHtml}

      <script>
        window.onload = function() {
          setTimeout(function() {
            window.print();
          }, 300);
        };
      </script>
    </body>
    </html>
  `);

  printWindow.document.close();
}
