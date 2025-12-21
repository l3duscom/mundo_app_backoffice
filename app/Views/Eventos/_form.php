<input type="hidden" name="user_id" value="<?php echo usuario_logado()->id; ?>">
<input type="hidden" name="slug" value="<?php echo esc($evento->slug); ?>">
<div class="col-lg-10 mx-auto mt-4">
    <div class="d-flex align-items-center justify-content-between">
        <div>

        </div>
        <div style="padding-right: 5px;">
            <a href="<?php echo site_url('eventos'); ?>">Voltar</a>
        </div>
    </div>
    <div class="card shadow radius-10">
        <div class="card-body">
            <h6 class="mb-0 text-uppercase">onde o seu evento vai acontecer?</h6>
            <hr />
            <div class="row">
                <div class="form-group col-md-12">
                    <label class="form-control-label">Local de realização do evento</label>
                    <input type="text" name="local" placeholder="Exemplo: Centro de eventos" class="form-control form-control-lg mb-2" style="font-size:medium; padding:13px" value="<?php echo esc($evento->local); ?>">
                </div>
                <div class="form-group col-md-4">
                    <label class="form-control-label">CEP</label>
                    <input type="text" name="cep" placeholder="Insira o CEP" class="form-control form-control-lg mb-2 cep" style="font-size:medium; padding:13px" value="<?php echo esc($evento->cep); ?>">
                    <div id="cep"></div>
                </div>

                <div class="form-group col-md-6">
                    <label class="form-control-label">Endereço</label>
                    <input type="text" name="endereco" placeholder="Insira o endereço" class="form-control form-control-lg mb-2" style="font-size:medium; padding:12px" value="<?php echo esc($evento->endereco); ?>">
                </div>

                <div class="form-group col-md-2">
                    <label class="form-control-label">Nº</label>
                    <input type="text" name="numero" placeholder="Insira o Nº" class="form-control form-control-lg mb-2" style="font-size:medium; padding:13px" value="<?php echo esc($evento->numero); ?>">
                </div>

                <div class="form-group col-md-4">
                    <label class="form-control-label">Bairro</label>
                    <input type="text" name="bairro" placeholder="Insira o Bairro" class="form-control form-control-lg mb-2" style="font-size:medium; padding:12px" value="<?php echo esc($evento->bairro); ?>">
                </div>

                <div class="form-group col-md-6">
                    <label class="form-control-label">Cidade</label>
                    <input type="text" name="cidade" placeholder="Insira a Cidade" class="form-control form-control-lg mb-2" style="font-size:medium; padding:12px" value="<?php echo esc($evento->cidade); ?>">
                </div>

                <div class="form-group col-md-2">
                    <label class="form-control-label">Estado</label>
                    <input type="text" name="estado" placeholder="U.F" class="form-control form-control-lg mb-2" style="font-size:medium; padding:12px" value="<?php echo esc($evento->estado); ?>">
                </div>
            </div>

        </div>
    </div>
    <div class="card shadow radius-10">
        <div class="card-body">
            <h6 class="mb-0 text-uppercase">Informações Básicas</h6>
            <label class="form-control-label">Adicione as principais informações do evento.</label>
            <hr />
            <div class="row">
                <div class="form-group col-md-12">
                    <label class="form-control-label">Nome do evento</label>
                    <input type="text" name="nome" placeholder="Nome do evento" class="form-control form-control-lg mb-2" style="font-size:medium; padding:13px" value="<?php echo esc($evento->nome); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label class="form-control-label">Produtora</label>
                    <input type="text" name="produtora" placeholder="Nome da produtora" class="form-control form-control-lg mb-2" style="font-size:medium; padding:13px" value="<?php echo esc($evento->produtora); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label class="form-control-label">Assunto</label>
                    <select name="assunto" class="form-select form-select-lg text-muted mb-3" style="font-size:medium; padding:13px" aria-label=".form-select-lg example">
                        <option value="">Selecione um assunto</option>
                        <option value="Convenção Geek" <?php echo ($evento->assunto == 'Convenção Geek') ? 'selected' : ''; ?>>Convenção Geek</option>
                        <option value="Festival de Anime" <?php echo ($evento->assunto == 'Festival de Anime') ? 'selected' : ''; ?>>Festival de Anime</option>
                        <option value="Evento de Cosplay" <?php echo ($evento->assunto == 'Evento de Cosplay') ? 'selected' : ''; ?>>Evento de Cosplay</option>
                        <option value="Convenção de Games" <?php echo ($evento->assunto == 'Convenção de Games') ? 'selected' : ''; ?>>Convenção de Games</option>
                        <option value="Outro" <?php echo ($evento->assunto == 'Outro') ? 'selected' : ''; ?>>Outro</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label class="form-control-label">Categoria</label>
                    <select name="categoria" class="form-select form-select-lg text-muted mb-3" style="font-size:medium; padding:13px" aria-label=".form-select-lg example">
                        <option value="">Selecione uma categoria</option>
                        <option value="Convenção" <?php echo ($evento->categoria == 'Convenção') ? 'selected' : ''; ?>>Convenção</option>
                        <option value="Festival" <?php echo ($evento->categoria == 'Festival') ? 'selected' : ''; ?>>Festival</option>
                        <option value="Workshop" <?php echo ($evento->categoria == 'Workshop') ? 'selected' : ''; ?>>Workshop</option>
                        <option value="Palestra" <?php echo ($evento->categoria == 'Palestra') ? 'selected' : ''; ?>>Palestra</option>
                        <option value="Outro" <?php echo ($evento->categoria == 'Outro') ? 'selected' : ''; ?>>Outro</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow radius-10">
        <div class="card-body">
            <h6 class="mb-0 text-uppercase">Imagens do Evento</h6>
            <label class="form-control-label">Adicione as imagens de avatar e capa do evento.</label>
            <hr />
            <div class="row">
                <!-- Avatar -->
                <div class="form-group col-md-6">
                    <label class="form-control-label"><i class="bx bx-user-circle me-1"></i>Avatar</label>
                    <input type="file" name="avatar_file" class="form-control" accept="image/jpeg,image/png">
                    <small class="text-muted">JPG ou PNG. Tamanho livre. Máximo 5MB.</small>
                    <?php if (!empty($evento->avatar)): ?>
                    <div class="mt-2">
                        <img src="<?= site_url('eventos/imagem/' . $evento->avatar) ?>" 
                             class="img-thumbnail" style="max-height: 100px;">
                        <small class="d-block text-muted">Imagem atual</small>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Cover -->
                <div class="form-group col-md-6">
                    <label class="form-control-label"><i class="bx bx-image me-1"></i>Capa (Cover)</label>
                    <input type="file" name="cover_file" class="form-control" accept="image/jpeg,image/png">
                    <small class="text-muted">JPG ou PNG. Máximo 5MB. <strong class="text-primary">Dimensões: 1600 x 250 pixels</strong></small>
                    <?php if (!empty($evento->cover)): ?>
                    <div class="mt-2">
                        <img src="<?= site_url('eventos/imagem/' . $evento->cover) ?>" 
                             class="img-thumbnail" style="max-height: 100px;">
                        <small class="d-block text-muted">Imagem atual</small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow radius-10">
        <div class="card-body">
            <h6 class="mb-0 text-uppercase">Descrição do evento</h6>
            <label class="form-control-label">Conte todos os detalhes do seu evento, como a programação e os diferenciais da sua produção!</label>
            <hr />
            <div class="row">
                <div class="form-group col-md-12">
                    <label for="descricao">Descrição</label>
                    <textarea class="form-control form-control-lg mb-2" name="descricao" rows="5"><?= $evento->descricao; ?></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow radius-10">
        <div class="card-body">
            <h6 class="mb-0 text-uppercase">Data e horário</h6>
            <label class="form-control-label">Informe aos participantes quando seu evento vai acontecer.</label>
            <hr />
            <div class="row">

                <div class="form-group col-md-4">
                    <label class="form-label">Data de início</label>
                    <input type="text" name="data_inicio" class="form-control datepicker" value="<?php echo $evento->data_inicio ? date('d/m/Y', strtotime($evento->data_inicio)) : ''; ?>" />
                </div>
                <div class="form-group col-md-2">
                    <label class="form-label">Hora de início</label>
                    <input type="text" name="hora_inicio" class="form-control timepicker" value="<?php echo $evento->hora_inicio; ?>" />
                </div>
                <div class="form-group col-md-4">
                    <label class="form-label">Data de fim</label>
                    <input type="text" name="data_fim" class="form-control datepicker" value="<?php echo $evento->data_fim ? date('d/m/Y', strtotime($evento->data_fim)) : ''; ?>" />
                </div>
                <div class="form-group col-md-2">
                    <label class="form-label">Hora de fim</label>
                    <input type="text" name="hora_fim" class="form-control timepicker" value="<?php echo $evento->hora_fim; ?>" />
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow radius-10">
        <div class="card-body">
            <h6 class="mb-0 text-uppercase">Meta Pixel</h6>
            <label class="form-control-label">Configure o Meta Pixel para rastreamento de conversões.</label>
            <hr />
            <div class="row">
                <div class="form-group col-md-12">
                    <label class="form-control-label">Meta Pixel ID</label>
                    <input type="text" name="meta_pixel_id" placeholder="Ex: 123456789012345" class="form-control form-control-lg mb-2" style="font-size:medium; padding:13px" value="<?php echo esc($evento->meta_pixel_id); ?>">
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow radius-10">
        <div class="card-body">
            <h6 class="mb-0 text-uppercase">Configurações</h6>
            <hr />
            <div class="row">
                <div class="col-12">
                    <div class="form-group col-md-12">
                        <label class="form-control-label">Como devemos chamar seu ticket?</label>
                        <select name="nomenclatura" class="form-select form-select-lg text-muted mb-3" style="font-size:medium; padding:13px" aria-label=".form-select-lg example">
                            <option value="">Selecione a nomenclatura</option>
                            <option value="ingresso" <?php echo ($evento->nomenclatura == 'ingresso') ? 'selected' : ''; ?>>Ingresso</option>
                            <option value="inscrição" <?php echo ($evento->nomenclatura == 'inscrição') ? 'selected' : ''; ?>>Inscrição</option>
                            <option value="credencial" <?php echo ($evento->nomenclatura == 'credencial') ? 'selected' : ''; ?>>Credencial</option>
                            <option value="contribuição" <?php echo ($evento->nomenclatura == 'contribuição') ? 'selected' : ''; ?>>Contribuição</option>
                        </select>
                    </div>
                    <div class="row" style="margin: 1px;">
                        <div class="form-check  form-switch col-lg-4 mb-3" style="font-size: medium; ">
                            <input type="hidden" name="taxa" value="0">
                            <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" name="taxa" value="1" id="taxa" <?php if ($evento->taxa == true) : ?> checked <?php endif; ?>>
                            <label class="form-check-label" for="flexSwitchCheckChecked">Absorver taxa de serviço</label>
                        </div>
                        <div class="form-check  form-switch col-lg-4 mb-3" style="font-size: medium; ">
                            <input type="hidden" name="integracao" value="0">
                            <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" name="integracao" value="1" id="integracao" <?php if ($evento->integracao == true) : ?> checked <?php endif; ?>>
                            <label class="form-check-label" for="flexSwitchCheckChecked">Integração com outra plataforma</label>
                        </div>

                        <div class="form-check  form-switch col-lg-4 mb-3" style="font-size: medium; ">
                            <input type="hidden" name="proprio" value="0">
                            <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" name="proprio" value="1" id="proprio" <?php if ($evento->proprio == true) : ?> checked <?php endif; ?>>
                            <label class="form-check-label" for="flexSwitchCheckChecked">Evento próprio</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow radius-10">
        <div class="card-body">
            <h6 class="mb-0 text-uppercase">Responsabilidades</h6>
            <hr />
            <div class="row">
                <div class="row" style="margin: 1px;">
                    <div class="form-check  form-switch  mb-3" style="font-size: medium; ">
                        <input type="hidden" name="ativo" value="0">
                        <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" name="ativo" value="1" id="ativo" <?php if ($evento->ativo == true) : ?> checked <?php endif; ?>>
                        <label class="form-check-label" for="flexSwitchCheckChecked">Ao publicar este evento, estou de acordo com os Termos de uso, com as Diretrizes de Comunidade e com o Acordo de Processamento de Dados, bem como declaro estar ciente da Política de Privacidade, da Política de Cookies e das Obrigatoriedades Legais.</label>
                    </div>
                </div>
            </div>
        </div>
    </div>