<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />



<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">


    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3"> <?= $concurso->nome ?></div>

    </div>

    <!--end breadcrumb-->
    
    <!-- Campo de Busca -->
    <div class="col-12 mb-3">
        <div class="card shadow radius-10">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent"><i class="bx bx-search"></i></span>
                            <input type="text" id="filtro-busca" class="form-control" placeholder="Buscar por nome, nome social, grupo, código ou email...">
                            <button type="button" id="btn-limpar" class="btn btn-outline-secondary d-none" onclick="limparFiltro()">
                                <i class="bx bx-x"></i> Limpar
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="btn-group w-100" role="group" aria-label="Filtro por categoria">
                            <button type="button" class="btn btn-primary filtro-categoria active" data-categoria="">
                                <i class="bx bx-list-ul"></i> Todos
                            </button>
                            <button type="button" class="btn btn-outline-secondary filtro-categoria" data-categoria="solo">
                                <i class="bx bx-user"></i> Solo
                            </button>
                            <button type="button" class="btn btn-outline-secondary filtro-categoria" data-categoria="dupla">
                                <i class="bx bx-group"></i> Dupla
                            </button>
                            <button type="button" class="btn btn-outline-secondary filtro-categoria" data-categoria="grupo">
                                <i class="bx bx-group"></i> Grupo
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2 text-end">
                        <span id="contador-resultados" class="badge bg-primary"><?= count($inscricoes) ?> inscrição(ões)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="lista-inscricoes">
        <?php foreach ($inscricoes as $inscricao) : ?>
            <div class="col-lg-12 inscricao-card" 
                 data-nome="<?= esc(strtolower($inscricao->nome ?? '')) ?>" 
                 data-nome-social="<?= esc(strtolower($inscricao->nome_social ?? '')) ?>" 
                 data-grupo="<?= esc(strtolower($inscricao->grupo ?? '')) ?>" 
                 data-email="<?= esc(strtolower($inscricao->email ?? '')) ?>"
                 data-codigo="<?= esc(strtolower($inscricao->codigo ?? '')) ?>"
                 data-categoria="<?= esc(strtolower($inscricao->categoria ?? '')) ?>">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card shadow radius-10">
                            <div class="card-body">

                                <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                                    <?php if ($inscricao->categoria != 'solo') : ?>
                                        <div class="breadcrumb-title pe-3"><strong class="font-20"><?= $inscricao->grupo ?></strong></div><span class="badge bg-info ms-2"><?= ucfirst($inscricao->categoria) ?></span>
                                    <?php else : ?>
                                        <div class="breadcrumb-title pe-3"><strong class="font-20"><?= $inscricao->nome_social ?></strong></div><span class="badge bg-secondary ms-2">Solo</span>
                                    <?php endif; ?>

                                    <div class="ps-3">
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb mb-0 p-0">
                                                <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-star"></i></a>
                                                </li>
                                                <li class="breadcrumb-item active" aria-current="page">
                                                    <?= $inscricao->codigo ?>

                                                </li>
                                            </ol>
                                        </nav>
                                    </div>
                                    <div class="ms-auto">
                                        <div class="btn-group mt-2">
                                            <?php if ($inscricao->status == 'INICIADA' || $inscricao->status == 'CANCELADA' || $inscricao->status == 'EDITADA') : ?>
                                                <a href="<?= site_url('/concursos/aprovaInscricao/' . $inscricao->id) ?>" class="btn btn-success mt-0 shadow">Aprovar inscrição</a>
                                                <a href="<?= site_url('/concursos/rejeitaInscricao/' . $inscricao->id) ?>" class="btn btn-danger mt-0 shadow">Rejeitar inscrição</a>
                                            <?php elseif ($inscricao->status == 'APROVADA') : ?>
                                                <a href="<?= site_url('/concursos/checkinonline/' . $inscricao->id) ?>" class="btn btn-info mt-0 shadow">Realizar Checkin Online</a>
                                                <a href="<?= site_url('/concursos/cancelaInscricao/' . $inscricao->id) ?>" class="btn btn-white mt-0 shadow">Cancelar / Desclassificar</a>
                                            <?php elseif ($inscricao->status == 'CHECKIN-ONLINE') : ?>
                                                <a href="<?= site_url('/concursos/checkin/' . $inscricao->id) ?>" class="btn btn-warning mt-0 shadow">Realizar Checkin</a>
                                                <a href="<?= site_url('/concursos/cancelaInscricao/' . $inscricao->id) ?>" class="btn btn-white mt-0 shadow">Cancelar / Desclassificar</a>
                                            <?php elseif ($inscricao->status == 'CHECKIN') : ?>
                                                <a href="<?= site_url('/concursos/avaliacao_kpop/' . $inscricao->id) ?>" class="btn btn-dark mt-0 shadow disabled">Checkin realizado em <?= date('d/m/Y H:i:s', strtotime($inscricao->updated_at)) ?></a>
                                                <a href="<?= site_url('/concursos/avaliacao_kpop/' . $inscricao->id) ?>" class="btn btn-primary mt-0 shadow">Iniciar avaliação</a>
                                            <?php elseif ($inscricao->status == 'REJEITADA') : ?>
                                                <a href="<?= site_url('/concursos/aprovaInscricao/' . $inscricao->id) ?>" class="btn btn-success mt-0 shadow ">Desfazer e aprovar</a>
                                            <?php endif; ?>

                                        </div>
                                    </div>

                                </div>
                                <div class="row">


                                    <hr class="mt-2">
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Status</p>

                                        <?php
                                        $statusClasses = [
                                            'INICIADA' => 'bg-warning text-dark',
                                            'APROVADA' => 'bg-success',
                                            'REJEITADA' => 'bg-danger',
                                            'CHECKIN-ONLINE' => 'bg-info',
                                            'CHECKIN' => 'bg-primary',
                                            'CANCELADA' => 'bg-secondary',
                                            'EDITADA' => 'bg-info',
                                        ];
                                        $statusClass = $statusClasses[$inscricao->status] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= $inscricao->status ?></span>
                                    </div>
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Whatsapp</p>

                                        <strong><a href="https://wa.me/55<?= str_replace(array("(", ")", " ", "-"), "", $inscricao->telefone) ?>" target="_blank"><?= $inscricao->telefone ?></a> </strong>
                                    </div>
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">E-mail</p>

                                        <strong><a href="mailto:<?= $inscricao->email ?>" target="_blank"><?= $inscricao->email ?></a> </strong>
                                    </div>
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">CPF</p>

                                        <strong><?= $inscricao->cpf ?> </strong>
                                    </div>
                                    <div class="mt-2"></div>
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Categoria</p>

                                        <strong><?= ucfirst($inscricao->categoria) ?> </strong>
                                    </div>
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Inscrito em</p>

                                        <strong><?= date('d/m/Y H:i', strtotime($inscricao->created_at)) ?> </strong>
                                    </div>
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Nome Social</p>

                                        <strong><?= $inscricao->nome_social ?> </strong>
                                    </div>
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Nome da Música</p>

                                        <strong><?= $inscricao->nome_musica ?? 'Não informado' ?> </strong>
                                    </div>
                                    
                                    <div class="mt-3"></div>
                                    
                                    <!-- Arquivos e Links -->
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Vídeo de Apresentação</p>
                                        <?php if (!empty($inscricao->video_apresentacao)) : ?>
                                            <a href="<?= $inscricao->video_apresentacao ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-play-circle"></i> Abrir Vídeo
                                            </a>
                                        <?php else : ?>
                                            <span class="text-muted">Não informado</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Imagem de Referência</p>
                                        <?php if (!empty($inscricao->referencia)) : ?>
                                            <a href="<?= site_url("concursos/imagem/$inscricao->referencia"); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="bx bx-image"></i> Ver Imagem
                                            </a>
                                        <?php else : ?>
                                            <span class="text-muted">Não informado</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Música</p>
                                        <?php if (!empty($inscricao->musica)) : ?>
                                            <a href="<?= site_url("concursos/imagem/$inscricao->musica"); ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                                <i class="bx bx-music"></i> Baixar Música
                                            </a>
                                        <?php else : ?>
                                            <span class="text-muted">Não informado</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Vídeo LED</p>
                                        <?php if (!empty($inscricao->video_led)) : ?>
                                            <a href="<?= site_url("concursos/imagem/$inscricao->video_led"); ?>" target="_blank" class="btn btn-sm btn-outline-warning">
                                                <i class="bx bx-video"></i> Baixar Vídeo
                                            </a>
                                        <?php else : ?>
                                            <span class="text-muted">Não informado</span>
                                        <?php endif; ?>
                                    </div>

                                    <?php 
                                    $historicoModel = new \App\Models\InscricaoHistoricoModel();
                                    $qtdEdicoes = $historicoModel->contaEdicoes($inscricao->id);
                                    ?>
                                    <div class="col-lg-12 mt-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Histórico de Edições</p>
                                        <?php if ($qtdEdicoes > 0): ?>
                                            <a href="<?= site_url("concursos/historico_edicoes/{$inscricao->id}") ?>" class="badge bg-info"><?= $qtdEdicoes ?> edição(ões)</a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Sem edições</span>
                                        <?php endif; ?>
                                    </div>




                                </div>
                                <hr class="mt-2">




                            </div>
                        </div>
                    </div>




                </div>




            </div> <!-- fecha inscricao-card -->
        <?php endforeach; ?>

            <hr>



            </div>


    </div>
</div>






<?php echo $this->endSection() ?>


<?php echo $this->section('scripts') ?>


<script type="text/javascript" src="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.js') ?>"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filtroInput = document.getElementById('filtro-busca');
    const btnLimpar = document.getElementById('btn-limpar');
    const contador = document.getElementById('contador-resultados');
    const cards = document.querySelectorAll('.inscricao-card');
    const totalCards = cards.length;
    const botoesCategoria = document.querySelectorAll('.filtro-categoria');
    
    let categoriaAtual = '';
    
    // Função principal de filtragem
    function aplicarFiltros() {
        const termo = filtroInput.value.toLowerCase().trim();
        let visiveis = 0;
        
        cards.forEach(card => {
            const nome = (card.getAttribute('data-nome') || '').toLowerCase();
            const nomeSocial = (card.getAttribute('data-nome-social') || '').toLowerCase();
            const grupo = (card.getAttribute('data-grupo') || '').toLowerCase();
            const email = (card.getAttribute('data-email') || '').toLowerCase();
            const codigo = (card.getAttribute('data-codigo') || '').toLowerCase();
            const categoria = (card.getAttribute('data-categoria') || '').toLowerCase();
            
            const textoCompleto = nome + ' ' + nomeSocial + ' ' + grupo + ' ' + email + ' ' + codigo + ' ' + categoria;
            
            // Verifica texto de busca
            const matchTexto = termo === '' || textoCompleto.includes(termo);
            
            // Verifica categoria
            const matchCategoria = categoriaAtual === '' || categoria === categoriaAtual;
            
            if (matchTexto && matchCategoria) {
                card.style.display = '';
                visiveis++;
            } else {
                card.style.display = 'none';
            }
        });
        
        contador.textContent = visiveis + ' de ' + totalCards + ' inscrição(ões)';
        
        if (termo.length > 0) {
            btnLimpar.classList.remove('d-none');
        } else {
            btnLimpar.classList.add('d-none');
        }
    }
    
    // Evento de busca por texto
    filtroInput.addEventListener('input', aplicarFiltros);
    
    // Eventos dos botões de categoria
    botoesCategoria.forEach(btn => {
        btn.addEventListener('click', function() {
            // Atualiza estado visual dos botões
            botoesCategoria.forEach(b => {
                b.classList.remove('btn-primary', 'active');
                b.classList.add('btn-outline-secondary');
            });
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-primary', 'active');
            
            // Atualiza categoria atual
            categoriaAtual = this.getAttribute('data-categoria');
            
            // Aplica filtros
            aplicarFiltros();
        });
    });
});

function limparFiltro() {
    const filtroInput = document.getElementById('filtro-busca');
    filtroInput.value = '';
    
    // Reset categoria para "Todos"
    const botoesCategoria = document.querySelectorAll('.filtro-categoria');
    botoesCategoria.forEach(b => {
        b.classList.remove('btn-primary', 'active');
        b.classList.add('btn-outline-secondary');
    });
    botoesCategoria[0].classList.remove('btn-outline-secondary');
    botoesCategoria[0].classList.add('btn-primary', 'active');
    
    filtroInput.dispatchEvent(new Event('input'));
    filtroInput.focus();
}
</script>



<?php echo $this->endSection() ?>