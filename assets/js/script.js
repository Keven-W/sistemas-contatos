/**
 * Sistema de Contatos - validações e interações do formulário.
 * A validação definitiva é feita também no servidor (processa.php).
 */
(function () {
    'use strict';

    const form = document.getElementById('form-contato');
    if (!form) {
        return;
    }

    const botao = document.getElementById('botao-enviar');
    const contador = document.getElementById('contador');
    const campoMensagem = document.getElementById('mensagem');
    const LIMITE_MENSAGEM = 2000;

    /* ---------- Regras de validação ---------- */
    const regras = {
        nome(valor) {
            const t = valor.trim();
            if (!t) return 'Informe seu nome.';
            if (t.length < 3 || t.length > 100) return 'O nome deve ter entre 3 e 100 caracteres.';
            if (!/^[\p{L}\s'.-]+$/u.test(t)) return 'O nome contém caracteres inválidos.';
            return '';
        },

        email(valor) {
            const t = valor.trim();
            if (!t) return 'Informe seu e-mail.';
            if (t.length > 150 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(t)) return 'Informe um e-mail válido.';
            return '';
        },

        telefone(valor) {
            const t = valor.trim();
            const digitos = t.replace(/\D/g, '');
            if (!t) return 'Informe seu telefone.';
            if (t.length > 20 || !/^[0-9()+\s-]+$/.test(t) || digitos.length < 10 || digitos.length > 13) {
                return 'Informe um telefone válido, com DDD.';
            }
            return '';
        },

        mensagem(valor) {
            const t = valor.trim();
            if (!t) return 'Escreva sua mensagem.';
            if (t.length < 10 || t.length > LIMITE_MENSAGEM) return 'A mensagem deve ter entre 10 e 2000 caracteres.';
            return '';
        }
    };

    /* ---------- Exibição de erros ---------- */
    function obterCampo(nome) {
        return form.elements.namedItem(nome);
    }

    function mostrarErro(nome, mensagem) {
        const campo = obterCampo(nome);
        const span = document.getElementById('erro-' + nome);

        if (span) {
            span.textContent = mensagem;
        }

        if (campo) {
            campo.setAttribute('aria-invalid', mensagem ? 'true' : 'false');
            campo.classList.toggle('valido', !mensagem && campo.value.trim() !== '');
        }
    }

    function validarCampo(nome) {
        const campo = obterCampo(nome);
        if (!campo || !regras[nome]) {
            return true;
        }
        const mensagem = regras[nome](campo.value);
        mostrarErro(nome, mensagem);
        return mensagem === '';
    }

    function validarFormulario() {
        let primeiroInvalido = null;

        Object.keys(regras).forEach(function (nome) {
            if (!validarCampo(nome) && !primeiroInvalido) {
                primeiroInvalido = obterCampo(nome);
            }
        });

        if (primeiroInvalido) {
            primeiroInvalido.focus();
        }

        return primeiroInvalido === null;
    }

    /* ---------- Máscara de telefone (formato brasileiro) ---------- */
    function aplicarMascaraTelefone(valor) {
        let digitos = valor.replace(/\D/g, '').slice(0, 11);

        if (digitos.length <= 10) {
            return digitos
                .replace(/^(\d{2})(\d)/, '($1) $2')
                .replace(/(\d{4})(\d)/, '$1-$2');
        }

        return digitos
            .replace(/^(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{5})(\d)/, '$1-$2');
    }

    /* ---------- Contador da mensagem ---------- */
    function atualizarContador() {
        if (!campoMensagem || !contador) {
            return;
        } 
        const tamanho = campoMensagem.value.length;
        contador.textContent = tamanho + '/' + LIMITE_MENSAGEM;
        contador.style.color = tamanho > LIMITE_MENSAGEM * 0.9 ? '#dc2626' : '';
    }

    /* ---------- Eventos ---------- */
    Object.keys(regras).forEach(function (nome) {
        const campo = obterCampo(nome);
        if (!campo) {
            return;
        }

        // Valida ao sair do campo
        campo.addEventListener('blur', function () {
            validarCampo(nome);
        });

        // Se já havia erro, revalida enquanto o usuário digita
        campo.addEventListener('input', function () {
            if (campo.getAttribute('aria-invalid') === 'true') {
                validarCampo(nome);
            }
        });
    });

    const campoTelefone = obterCampo('telefone');
    if (campoTelefone) {
        campoTelefone.addEventListener('input', function () {
            campoTelefone.value = aplicarMascaraTelefone(campoTelefone.value);
        });
    }

    if (campoMensagem) {
        campoMensagem.addEventListener('input', atualizarContador);
        atualizarContador();
    }

    form.addEventListener('submit', function (evento) {
        if (!validarFormulario()) {
            evento.preventDefault();
            return;
        }

        // Evita envio duplicado
        if (botao) {
            botao.disabled = true;
            botao.textContent = 'Enviando...';
        }
    });

    // Destaca o alerta de sucesso e o remove da tela após alguns segundos
    const alertaSucesso = document.querySelector('.alerta-sucesso');
    if (alertaSucesso) {
        setTimeout(function () {
            alertaSucesso.style.transition = 'opacity 0.6s';
            alertaSucesso.style.opacity = '0';
            setTimeout(function () {
                alertaSucesso.remove();
            }, 700);
        }, 8000);
    }
})();
