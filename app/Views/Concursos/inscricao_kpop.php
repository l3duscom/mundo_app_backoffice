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
                                    <div id="response"></div>

                                    <div class="card shadow radius-10">
                                        <div class="card-body">
                                            <?php echo form_open_multipart('Concursos/registrar_inscricao_kpop_open', ['id' => 'form-inscricao']) ?>

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
                                                    <label class="form-control-label text-muted" style="padding-left: 5px;"> Possui algum video de apresentação?</label>
                                                    <input type="text" name="video_apresentacao" placeholder="https://" class="form-control  mb-2 shadow " style="padding:13px;" required>
                                                    <label class="form-control-label text-muted mb-3" style="font-size: 10px; padding-left:5px;"><i class="fadeIn animated bx bx-info-circle" style="  font-size: 13px; font-weight: 600px"></i>
                                                        Informe o link de algum video seu ou do seu grupo em alguma apresentação anterior. (link do Youtube, google drive ou similar). Esta video não será divulgado, e servirá como ateste para triagem (Fase classificatória) e confirmação da sua inscrição!</label>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label class="form-control-label">Nome do Grupo/dupla</label>
                                                    <input type="text" name="grupo" placeholder="Informe o nome do grupo" class="form-control mb-2 shadow " style="padding:13px;">
                                                    <label class="form-control-label text-muted mb-3" style="font-size: 10px; padding-left:5px;"><i class="fadeIn animated bx bx-info-circle" style="  font-size: 13px; font-weight: 600px"></i>
                                                        Preencha apenas em caso de inscrição de grupo/dupla. A divulgação será feita usando esse nome.</label>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label class="form-control-label">Integrantes</label>

                                                    <input type="number" name="integrantes" placeholder="Integrantes" class="form-control mb-2 shadow " style="padding:13px;">
                                                    <label class="form-control-label text-muted mb-3" style="font-size: 10px; padding-left:5px;"><i class="fadeIn animated bx bx-info-circle" style="  font-size: 13px; font-weight: 600px"></i>
                                                        Preencha apenas em caso de inscrição de grupo. Quantos integrantes tem o seu grupo?</label>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label class="form-control-label">Categoria</label>

                                                    <select id="categoria" name="categoria" class="form-control mb-2 shadow">
                                                        <option value="---">Categoria</option>
                                                        <option value="grupo">Grupo</option>
                                                        <option value="dupla">Dupla</option>
                                                        <option value="solo">Solo</option>
                                                    </select>
                                                    <label class="form-control-label text-muted mb-3" style="font-size: 10px; padding-left:5px;"><i class="fadeIn animated bx bx-info-circle" style="  font-size: 13px; font-weight: 600px"></i>
                                                        Em qual categoria você irá competir?</label>
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
                                                                        Figurino: 1 (uma) imagem .Jpeg, colorida.</label>
                                                                </div>
                                                                <div class="form-group col-md-12">
                                                                    <label class="form-control-label">Música <span style="color: red;"> Máx. 50mb</span></label>
                                                                    <input type="file" name="musica" class="form-control" required>
                                                                    <label class="form-control-label text-muted mb-3" style="font-size: 10px; padding-left:5px;"><i class="fadeIn animated bx bx-info-circle" style="  font-size: 13px; font-weight: 600px"></i>
                                                                        Arquivo MP3 com música completa. Esta música será usada na sua apresentação.</label>
                                                                </div>
                                                                <div class="form-group col-md-12">
                                                                    <label class="form-control-label">Vídeo LED <a href="https://www.youtube.com/watch?v=gSoFw92w-zo" target="_blank"><u>( Ver Exemplo )</u></a> <span style="color: red;"> Máx. 100mb</span></label>
                                                                    <input type="file" name="video_led" class="form-control" required>
                                                                    <label class="form-control-label text-muted mb-3" style="font-size: 10px; padding-left:5px;"><i class="fadeIn animated bx bx-info-circle" style="  font-size: 13px; font-weight: 600px"></i>
                                                                        Arquivo MP4. Se você possui um video que queira que seja utilizado na sua divulgação. <strong>Exemplo:</strong> <a href="https://www.youtube.com/watch?v=gSoFw92w-zo" target="_blank"><u>Ver exemplo usado na última competição!</u></a> </label>
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

<!-- Modal de Sucesso -->
<div class="modal fade" id="modalSucesso" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalSucessoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="text-align:center;">
      <div class="modal-body py-5">
        <div class="mb-4">
            <i class="bx bx-check-circle" style="font-size: 5rem; color: #28a745;"></i>
        </div>
        <h4 class="mb-3 text-success">Inscrição Realizada com Sucesso!</h4>
        <p class="text-muted mb-4">Sua inscrição foi registrada com sucesso.<br>Em breve você receberá um e-mail de confirmação.</p>
        <button type="button" class="btn btn-success" onclick="window.location.href='<?= site_url('/') ?>'">Entendi</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Erro -->
<div class="modal fade" id="modalErro" tabindex="-1" aria-labelledby="modalErroLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="text-align:center;">
      <div class="modal-body py-5">
        <div class="mb-4">
            <i class="bx bx-error-circle" style="font-size: 5rem; color: #dc3545;"></i>
        </div>
        <h4 class="mb-3 text-danger">Erro ao Processar Inscrição</h4>
        <p class="text-muted mb-4" id="mensagemErro">Ocorreu um erro ao processar sua inscrição. Por favor, tente novamente.</p>
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<script src="<?php echo site_url('recursos/vendor/loadingoverlay/loadingoverlay.min.js') ?>"></script>
<script src="<?php echo site_url('recursos/vendor/mask/jquery.mask.min.js') ?>"></script>
<script src="<?php echo site_url('recursos/vendor/mask/app.js') ?>"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-inscricao');
    const btn = document.getElementById('btn-salvar');
    const btnText = document.getElementById('btn-text');
    const btnSpinner = document.getElementById('btn-spinner');
    
    if (form && btn) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
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
            
            // Envia o formulário via AJAX
            var formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                // Fecha modal de processamento
                modalProcessando.hide();
                
                // Tenta parsear como JSON primeiro
                try {
                    const data = JSON.parse(html);
                    
                    if (data.success) {
                        // Mostra modal de sucesso
                        var modalSucesso = new bootstrap.Modal(document.getElementById('modalSucesso'));
                        modalSucesso.show();
                    } else {
                        // Mostra modal de erro com mensagem específica
                        document.getElementById('mensagemErro').textContent = data.message || 'Erro ao processar inscrição. Por favor, tente novamente.';
                        var modalErro = new bootstrap.Modal(document.getElementById('modalErro'));
                        modalErro.show();
                        
                        // Reabilita o botão
                        btn.disabled = false;
                        btnText.textContent = 'Realizar Inscrição';
                        btnSpinner.classList.add('d-none');
                    }
                } catch (e) {
                    // Se não for JSON, trata como HTML (resposta padrão do CodeIgniter)
                    if (html.includes('sucesso') || html.includes('success')) {
                        var modalSucesso = new bootstrap.Modal(document.getElementById('modalSucesso'));
                        modalSucesso.show();
                    } else {
                        // Mostra a resposta HTML no div response
                        document.getElementById('response').innerHTML = html;
                        
                        // Scroll até o topo para ver a mensagem
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        
                        // Reabilita o botão
                        btn.disabled = false;
                        btnText.textContent = 'Realizar Inscrição';
                        btnSpinner.classList.add('d-none');
                    }
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                modalProcessando.hide();
                
                // Mostra modal de erro
                document.getElementById('mensagemErro').textContent = 'Erro de conexão. Por favor, verifique sua internet e tente novamente.';
                var modalErro = new bootstrap.Modal(document.getElementById('modalErro'));
                modalErro.show();
                
                // Reabilita o botão
                btn.disabled = false;
                btnText.textContent = 'Realizar Inscrição';
                btnSpinner.classList.add('d-none');
            });
            
            return false;
        });
    }
});
</script>

<?php echo $this->endSection() ?>