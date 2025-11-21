<?php echo $this->extend('Layout/externo'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">
    <div class="col-lg-2 ">
    </div>
    <div class="col-lg-8 ">
        <div class="block">
            <div class="block-body">
                <div class="card shadow radius-10">
                    <div class="card-body">






                        <div class="col-lg-12">

                            <div class="block">

                                <div class="block-body">

                                    <!-- Exibirá os retornos do backend -->
                                    <div id="response">
                                        <?php if (session()->has('sucesso')): ?>
                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <i class="bx bx-check-circle me-2"></i>
                                                <strong>Sucesso!</strong> <?= session('sucesso') ?>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (session()->has('atencao')): ?>
                                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                                <i class="bx bx-error-circle me-2"></i>
                                                <strong>Atenção!</strong> <?= session('atencao') ?>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (session()->has('erro')): ?>
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <i class="bx bx-x-circle me-2"></i>
                                                <strong>Erro!</strong> <?= session('erro') ?>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="card shadow radius-10">
                                        <div class="card-body">
                                            <?php echo form_open_multipart('Concursos/registrar_inscricao_cosplay_open_apresentacao', ['id' => 'form-inscricao']) ?>

                                            <?= csrf_field() ?>

                                            <input type="hidden" name="concurso_id" value="<?= $concurso->id ?>">
                                            <center>
                                                <h4>
                                                    <?= $concurso->nome ?>
                                                </h4>
                                                <hr>
                                            </center>
                                            <div class="alert alert-danger  fade show" role="alert">
                                                <strong>Atenção</strong> Todos os campos são obrigatórios!

                                            </div>

                                            <div class="row">

                                                <div class="form-group col-md-12">
                                                    <label class="form-control-label text-muted" style="padding-left: 5px;"> Informe o seu melhor e-mail</label>
                                                    <input type="email" name="email" placeholder="Informe seu email" class="form-control  mb-0 shadow" style="padding:13px;" required>
                                                    <label class="form-control-label text-muted mb-3" style="font-size: 10px; padding-left:5px;"><i class="fadeIn animated bx bx-info-circle" style="  font-size: 13px; font-weight: 600px"></i>
                                                        Este e-mail será usado para confirmar a sua inscrição, e lhe dar acesso ao mundo dream, para realziar check-in, acompanhar e validar as suas notas!</label>

                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label class="form-control-label text-muted" style="padding-left: 5px;"> Informe o seu Nome Social</label>
                                                    <input type="text" name="nome_social" placeholder="Informe seu nome social" class="form-control  mb-2 shadow " style="padding:13px;" required>
                                                    <label class="form-control-label text-muted mb-3" style="font-size: 10px; padding-left:5px;"><i class="fadeIn animated bx bx-info-circle" style="  font-size: 13px; font-weight: 600px"></i>
                                                        Esta informação será a única usada na divulgação da sua participação na competição.</label>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label class="form-control-label text-muted" style="padding-left: 5px;"> Informe o seu nome, igual o do RG</label>
                                                    <input type="text" name="nome" placeholder="Nome completo" class="form-control  mb-2 shadow " style="padding:13px;" required>
                                                    <label class="form-control-label text-muted mb-3" style="font-size: 10px; padding-left:5px;"><i class="fadeIn animated bx bx-info-circle" style="  font-size: 13px; font-weight: 600px"></i>
                                                        Esta informação não será divulgada. Ela é usada unicamente para conferência com seu documento oficial.</label>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label class="form-control-label">Celular/Whatsapp</label>
                                                    <input type="text" name="telefone" placeholder="Insira o telefone" class="form-control sp_celphones mb-2 shadow" style="font-size:medium; padding:13px" required>
                                                    <label class="form-control-label text-muted mb-3" style="font-size: 10px; padding-left:5px;"><i class="fadeIn animated bx bx-info-circle" style="  font-size: 13px; font-weight: 600px"></i>
                                                        É por aqui que manteremos contato referente à sua participação na competição!</label>
                                                </div>

                                                <div class="form-group col-md-6">
                                                    <label class="form-control-label">CPF</label>
                                                    <input type="text" name="cpf" placeholder="Digite o número do  seu CPF" class="form-control  mb-2 shadow cpf" style="font-size:medium; padding:13px" required>
                                                    <label class="form-control-label text-muted mb-3" style="font-size: 10px; padding-left:5px;"><i class="fadeIn animated bx bx-info-circle" style="  font-size: 13px; font-weight: 600px"></i>
                                                        Esta informação não será divulgada. Ela é usada únicamente para sua identificação.</label>
                                                </div>
                                                <div class="form-group col-md-12">
                                                    <label class="form-control-label text-muted" style="padding-left: 5px;"> Qual sua motivação?</label>
                                                    <input type="text" name="motivacao" placeholder="" class="form-control  mb-2 shadow " style="padding:13px;" required>
                                                    <label class="form-control-label text-muted mb-3" style="font-size: 10px; padding-left:5px;"><i class="fadeIn animated bx bx-info-circle" style="  font-size: 13px; font-weight: 600px"></i>
                                                        O que te motiva a participar da competição?</label>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label class="form-control-label">Nome do personagem</label>
                                                    <input type="text" name="personagem" placeholder="Informe o nome do personagem" class="form-control mb-2 shadow " style="padding:13px;" required>
                                                    <label class="form-control-label text-muted mb-3" style="font-size: 10px; padding-left:5px;"><i class="fadeIn animated bx bx-info-circle" style="  font-size: 13px; font-weight: 600px"></i>
                                                        Exemplo: Luffy</label>
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label class="form-control-label">Nome da obra/mídia</label>
                                                    <input type="text" name="obra" placeholder="Informe o nome da obra" class="form-control mb-2 shadow " style="padding:13px;" required>
                                                    <label class="form-control-label text-muted mb-3" style="font-size: 10px; padding-left:5px;"><i class="fadeIn animated bx bx-info-circle" style="  font-size: 13px; font-weight: 600px"></i>
                                                        Exemplo: One Piece</label>
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label class="form-control-label">Que tipo de obra está baseando este cosplay?</label>

                                                    <select id="genero" name="genero" class="form-control mb-2 shadow">
                                                        <option value="---">Selecione uma opção</option>
                                                        <option value="Animação(Anime, Filme de animação, Cartoon, entre outros)">Animação(Anime, Filme de animação, Cartoon, entre outros)</option>
                                                        <option value="Game">Game</option>
                                                        <option value="Filme">Filme</option>
                                                        <option value="Série">Série</option>
                                                        <option value="Mangá/Manwa">Mangá/Manwa</option>
                                                        <option value="HQ">HQ</option>
                                                        <option value="Livro">Livro</option>
                                                    </select>

                                                </div>


                                                <div class="form-group col-md-12">
                                                    <label class="form-control-label text-muted" style="padding-left: 5px;">Observações</label>
                                                    <input type="text" name="observacoes" placeholder="" class="form-control  mb-2 shadow " style="padding:13px;" required>
                                                    <label class="form-control-label text-muted mb-3" style="font-size: 10px; padding-left:5px;"><i class="fadeIn animated bx bx-info-circle" style="  font-size: 13px; font-weight: 600px"></i>
                                                        Observações sobre o arquivo de Referência ou Apresentação? Ou sobre a apresentação em si? Exemplo se vai precisar de ajuda para subir ao palco, segurar algo, necessidade de algo não disponível como tomada ou algum outro detalhe não especificado anteriormente. Ou sobre a apresentação em si? Caso não tenha informe "Sem mais observações"</label>
                                                </div>

                                                <div class="block">

                                                    <div class="block-body">
                                                        <div class="card shadow radius-10">
                                                            <div class="card-body">
                                                                <span style="font-weight: 600; font-size:16px">Arquivos</span>
                                                                <hr>
                                                                <div class="form-group col-md-12">
                                                                    <label class="form-control-label">Imagem de referência <span style="color: red;"> Máx. 50mb</span></label>
                                                                    <input type="file" name="referencia" class="form-control" required>
                                                                    <label class="form-control-label text-muted mb-3" style="font-size: 10px; padding-left:5px;"><i class="fadeIn animated bx bx-info-circle" style="  font-size: 13px; font-weight: 600px"></i>
                                                                        REFERÊNCIA VISUAL OBRIGATÓRIA, insira seu arquivo de referência (Imagem de Referência) - SOMENTE 1 ARQUIVO. | Somente 1 Imagem de Referência em formato JPG ou PNG. (Preferencialmente no tamanho de 1200 x 675 pixels). Este arquivo é obrigatório para validar a inscrição.</label>
                                                                </div>
                                                                <div class="form-group col-md-12">
                                                                    <label class="form-control-label">Vídeo LED <a href="https://www.youtube.com/watch?v=gSoFw92w-zo" target="_blank"><u>( Ver Exemplo )</u></a> <span style="color: red;"> Máx. 100mb</span></label>
                                                                    <input type="file" name="video_led" class="form-control" required>
                                                                    <label class="form-control-label text-muted mb-3" style="font-size: 10px; padding-left:5px;"><i class="fadeIn animated bx bx-info-circle" style="  font-size: 13px; font-weight: 600px"></i></label>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>







                                                <div class="d-grid gap-2 mb-0 mt-3">
                                                    <button id="btn-salvar" type="submit" class="btn btn-primary btn-lg mt-0">
                                                        <span id="btn-text">Realizar Inscrição</span>
                                                        <span id="btn-spinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                                                    </button>
                                                    <center><span style="font-size: 12px;"><?= $concurso->nome ?></span></center>
                                                </div>
                                            </div>





                                            <?php echo form_close(); ?>
                                        </div>
                                    </div>

                                </div>



                            </div> <!-- ./ block -->

                        </div>





                    </div>


                </div>

            </div>
        </div>
    </div>
    <div class="col-lg-2 ">
    </div>


</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>

<!-- Modal de Processamento -->
<div class="modal fade" id="modalProcessando" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalProcessandoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="text-align:center;">
      <div class="modal-body py-5">
        <div class="spinner-border text-primary mb-3" style="width: 4rem; height: 4rem;" role="status">
            <span class="visually-hidden">Processando...</span>
        </div>
        <h5 class="mb-3 mt-2">Processando sua inscrição...</h5>
        <p class="text-muted">Não feche ou atualize esta página.<br>Estamos enviando seus dados e arquivos.</p>
        <small class="text-muted"><i class="bx bx-info-circle"></i> Isso pode levar alguns instantes dependendo do tamanho dos arquivos.</small>
      </div>
    </div>
  </div>
</div>

<script src="<?php echo site_url('recursos/vendor/loadingoverlay/loadingoverlay.min.js') ?>"></script>
<script src="<?php echo site_url('recursos/vendor/mask/jquery.mask.min.js') ?>"></script>
<script src="<?php echo site_url('recursos/vendor/mask/app.js') ?>"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Se houver mensagem de sucesso ou erro, faz scroll até ela
    const responseDiv = document.getElementById('response');
    if (responseDiv && responseDiv.querySelector('.alert')) {
        responseDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
        window.scrollBy(0, -100); // Ajusta para não ficar muito grudado no topo
    }
    
    const form = document.getElementById('form-inscricao');
    const btn = document.getElementById('btn-salvar');
    const btnText = document.getElementById('btn-text');
    const btnSpinner = document.getElementById('btn-spinner');
    
    if (form && btn) {
        form.addEventListener('submit', function(e) {
            // Valida campos obrigatórios
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }
            
            // Desabilita o botão e mostra spinner
            btn.disabled = true;
            btnText.textContent = 'Processando...';
            btnSpinner.classList.remove('d-none');
            
            // Mostra modal de processamento
            var modalProcessando = new bootstrap.Modal(document.getElementById('modalProcessando'));
            modalProcessando.show();
            
            // Deixa o formulário enviar normalmente (sem preventDefault)
            // O backend vai fazer redirect com mensagem flash
            return true;
        });
    }
});
</script>

<?php echo $this->endSection() ?>