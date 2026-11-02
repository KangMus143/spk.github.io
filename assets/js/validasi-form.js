function namaKolomValidasi(field) {
    const label = document.querySelector(`label[for="${field.id}"]`);
    return label ? label.textContent.trim().replace(/:$/, '') : 'Isian ini';
}

function pesanValidasiIndonesia(field) {
    const validity = field.validity;
    const nama = namaKolomValidasi(field);

    if (validity.customError) {
        return field.validationMessage;
    }
    if (validity.valueMissing) {
        return field.type === 'checkbox'
            ? 'Silakan centang pilihan ini.'
            : `${nama} wajib diisi.`;
    }
    if (validity.badInput) {
        return `${nama} harus berupa angka yang valid.`;
    }
    if (validity.typeMismatch) {
        if (field.type === 'email') {
            return `Masukkan alamat email yang valid untuk ${nama.toLowerCase()}.`;
        }
        if (field.type === 'url') {
            return `Masukkan alamat situs yang valid untuk ${nama.toLowerCase()}.`;
        }
        return `Format ${nama.toLowerCase()} tidak valid.`;
    }
    if (validity.patternMismatch) {
        return field.title || `Format ${nama.toLowerCase()} belum sesuai.`;
    }
    if (validity.tooShort) {
        return `${nama} harus terdiri dari sedikitnya ${field.minLength} karakter.`;
    }
    if (validity.tooLong) {
        return `${nama} tidak boleh lebih dari ${field.maxLength} karakter.`;
    }
    if (validity.rangeUnderflow) {
        return `${nama} tidak boleh kurang dari ${field.min}.`;
    }
    if (validity.rangeOverflow) {
        return `${nama} tidak boleh lebih dari ${field.max}.`;
    }
    if (validity.stepMismatch) {
        return `Masukkan nilai ${nama.toLowerCase()} dengan kelipatan yang benar.`;
    }
    return `${nama} belum diisi atau formatnya belum sesuai.`;
}

function perbaruiPesanValidasi(field) {
    field.setCustomValidity('');
    if (!field.validity.valid) {
        field.setCustomValidity(pesanValidasiIndonesia(field));
    }
}

document.querySelectorAll('form').forEach(form => {
    const fields = Array.from(form.querySelectorAll('input, select, textarea'))
        .filter(field => field.willValidate);

    form.noValidate = true;
    fields.forEach(field => {
        perbaruiPesanValidasi(field);
        field.addEventListener('input', () => perbaruiPesanValidasi(field));
        field.addEventListener('change', () => perbaruiPesanValidasi(field));
    });

    form.addEventListener('submit', event => {
        fields.forEach(perbaruiPesanValidasi);
        const invalidField = fields.find(field => !field.validity.valid);
        if (invalidField) {
            event.preventDefault();
            invalidField.reportValidity();
        }
    });
});
