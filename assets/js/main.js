// Auto-dismiss alerts
document.querySelectorAll('.alert[data-dismiss]').forEach(el => {
    setTimeout(() => el.remove(), 4000);
});

// Confirm delete
document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', e => {
        if (!confirm(btn.dataset.confirm || 'Confirmar ação?')) e.preventDefault();
    });
});

// CEP lookup
function buscaCEP(cepInput, prefix = '') {
    const cep = cepInput.value.replace(/\D/g,'');
    if (cep.length !== 8) return;
    fetch(`https://viacep.com.br/ws/${cep}/json/`)
        .then(r => r.json())
        .then(d => {
            if (d.erro) return;
            const f = s => document.querySelector(`[name="${prefix}${s}"]`);
            if (f('logradouro')) f('logradouro').value = d.logradouro || '';
            if (f('bairro'))     f('bairro').value     = d.bairro || '';
            if (f('cidade'))     f('cidade').value      = d.localidade || '';
            if (f('estado'))     f('estado').value      = d.uf || '';
            const n = document.querySelector(`[name="${prefix}numero"]`);
            if (n) n.focus();
        });
}

// Mask CPF
function maskCPF(v){
    return v.replace(/\D/g,'').slice(0,11)
            .replace(/(\d{3})(\d)/,'$1.$2')
            .replace(/(\d{3})(\d)/,'$1.$2')
            .replace(/(\d{3})(\d{1,2})$/,'$1-$2');
}
// Mask CNPJ
function maskCNPJ(v){
    return v.replace(/\D/g,'').slice(0,14)
            .replace(/^(\d{2})(\d)/,'$1.$2')
            .replace(/^(\d{2})\.(\d{3})(\d)/,'$1.$2.$3')
            .replace(/\.(\d{3})(\d)/,'.$1/$2')
            .replace(/(\d{4})(\d)/,'$1-$2');
}
// Mask phone
function maskPhone(v){
    v = v.replace(/\D/g,'').slice(0,11);
    if(v.length <= 10) return v.replace(/(\d{2})(\d{4})(\d{0,4})/,'($1) $2-$3');
    return v.replace(/(\d{2})(\d{5})(\d{0,4})/,'($1) $2-$3');
}

document.querySelectorAll('[data-mask="cpf"]').forEach(i=>i.addEventListener('input',e=>e.target.value=maskCPF(e.target.value)));
document.querySelectorAll('[data-mask="cnpj"]').forEach(i=>i.addEventListener('input',e=>e.target.value=maskCNPJ(e.target.value)));
document.querySelectorAll('[data-mask="phone"]').forEach(i=>i.addEventListener('input',e=>e.target.value=maskPhone(e.target.value)));
document.querySelectorAll('[data-mask="cep"]').forEach(i=>{
    i.addEventListener('input',e=>{
        let v=e.target.value.replace(/\D/g,'').slice(0,8);
        e.target.value=v.replace(/(\d{5})(\d)/,'$1-$2');
    });
    i.addEventListener('blur',e=>buscaCEP(e.target));
});

// Toggle pessoa física/jurídica
const tipoPessoa = document.getElementById('tipo_pessoa');
if (tipoPessoa) {
    const togglePessoa = () => {
        const pf = document.getElementById('campos_pf');
        const pj = document.getElementById('campos_pj');
        if (!pf || !pj) return;
        if (tipoPessoa.value === 'fisica') { pf.style.display=''; pj.style.display='none'; }
        else { pf.style.display='none'; pj.style.display=''; }
    };
    tipoPessoa.addEventListener('change', togglePessoa);
    togglePessoa();
}
