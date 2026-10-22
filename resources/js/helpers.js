/** Angka berformat Indonesia: 12.345 */
export const fmtNum = (n) => Number(n || 0).toLocaleString('id-ID');

/** 850 -> "850 g", 1500 -> "1,5 kg" */
export const fmtGram = (g) => {
    const v = Number(g || 0);
    return v >= 1000
        ? `${(v / 1000).toLocaleString('id-ID', { maximumFractionDigits: 3 })} kg`
        : `${v.toLocaleString('id-ID')} g`;
};

/** "2026-10-20" -> "20 Okt 2026" */
export const fmtTanggal = (iso) =>
    iso
        ? new Date(`${iso}T00:00:00`).toLocaleDateString('id-ID', {
              day: '2-digit',
              month: 'short',
              year: 'numeric',
          })
        : '-';

/** Tanggal hari ini (zona waktu perangkat) dalam format YYYY-MM-DD untuk input type="date". */
export const hariIni = () => new Date().toLocaleDateString('en-CA');

export const kategoriLabel = {
    organik: 'Organik',
    anorganik: 'An-organik',
    b3: 'B3',
};

export const kategoriClass = {
    organik: 'bg-lime-100 text-lime-900',
    anorganik: 'bg-sky-100 text-sky-900',
    b3: 'bg-amber-100 text-amber-900',
};

export const peranLabel = {
    admin: 'Admin',
    petugas: 'Petugas',
    siswa: 'Siswa',
    guru: 'Guru',
    tendik: 'Tenaga Administrasi',
};

/** Kunci unik per form agar kirim ganda tidak mencatat setoran dua kali (divalidasi ulang di server). */
export const kunciBaru = () =>
    globalThis.crypto?.randomUUID?.() ??
    'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;
        return (c === 'x' ? r : (r & 0x3) | 0x8).toString(16);
    });
