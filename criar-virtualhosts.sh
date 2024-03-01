#!/bin/bash

# Função para exibir instruções de uso
display_usage() {
    echo "Uso: $0 nome_do_virtualhost [nome_do_virtualhost2 ...]"
    echo "Exemplo: $0 host1 host2"
}

# Verificar se o script foi chamado com argumentos
if [ $# -eq 0 ]; then
    display_usage
    exit 1
fi

# Atualizar repositório e sistema
sudo apt update
if [ $? -eq 0 ]; then
    echo "Repositório atualizado com sucesso."
else
    echo "Falha ao atualizar o repositório."
    exit 1
fi

sudo apt upgrade -y
if [ $? -eq 0 ]; then
    echo "Sistema atualizado com sucesso."
else
    echo "Falha ao atualizar o sistema."
    exit 1
fi

# Instalar Apache
sudo apt install apache2 -y
if [ $? -eq 0 ]; then
    echo "Apache instalado com sucesso."
else
    echo "Falha ao instalar o Apache."
    exit 1
fi

# Reiniciar o Apache
sudo systemctl restart apache2
if [ $? -eq 0 ]; then
    echo "Apache reiniciado com sucesso."
else
    echo "Falha ao reiniciar o Apache."
    exit 1
fi

# Loop sobre cada argumento (nome do virtual host)
for virtualhost in "$@"; do
    # Verificar se o diretório do virtual host já existe
    if [ -d "/var/www/$virtualhost" ]; then
        # Remover o diretório existente
        sudo rm -rf /var/www/"$virtualhost"
        if [ $? -eq 0 ]; then
            echo "Diretório existente removido com sucesso para o virtual host $virtualhost."
        else
            echo "Falha ao remover o diretório existente para o virtual host $virtualhost."
            exit 1
        fi
    fi

    # Criar diretórios para o virtual host
    sudo mkdir -p /var/www/"$virtualhost"/public_html
    if [ $? -eq 0 ]; then
        echo "Diretórios criados com sucesso para o virtual host $virtualhost."
    else
        echo "Falha ao criar diretórios para o virtual host $virtualhost."
        exit 1
    fi

    # Alterar a propriedade dos diretórios
    sudo chown -R "$USER":"$USER" /var/www/"$virtualhost"/public_html
    if [ $? -eq 0 ]; then
        echo "Propriedade do diretório alterada com sucesso para o virtual host $virtualhost."
    else
        echo "Falha ao alterar a propriedade do diretório para o virtual host $virtualhost."
        exit 1
    fi

    # Definir permissões de acesso
    sudo chmod -R 755 /var/www
    if [ $? -eq 0 ]; then
        echo "Permissões de acesso definidas com sucesso para o virtual host $virtualhost."
    else
        echo "Falha ao definir permissões de acesso para o virtual host $virtualhost."
        exit 1
    fi

    # Criar página de índice para o virtual host
    sudo echo "Welcome to $virtualhost." > /var/www/"$virtualhost"/public_html/index.html
    if [ $? -eq 0 ]; then
        echo "Página de índice criada com sucesso para o virtual host $virtualhost."
    else
        echo "Falha ao criar página de índice para o virtual host $virtualhost."
        exit 1
    fi

    # Adicionar entrada ao arquivo hosts
    sudo sh -c "echo 127.0.0.1 www.$virtualhost.com >> /etc/hosts"
    if [ $? -eq 0 ]; then
        echo "Entrada adicionada ao arquivo hosts para o virtual host $virtualhost."
    else
        echo "Falha ao adicionar entrada ao arquivo hosts para o virtual host $virtualhost."
        exit 1
    fi

    # Criar arquivo de configuração do virtual host
    sudo cp /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/"$virtualhost".conf
    if [ $? -eq 0 ]; then
        echo "Arquivo de configuração criado com sucesso para o virtual host $virtualhost."
    else
        echo "Falha ao criar arquivo de configuração para o virtual host $virtualhost."
        exit 1
    fi

    # Editar arquivo de configuração do virtual host
    sudo sed -i "s/000-default/$virtualhost/g" /etc/apache2/sites-available/"$virtualhost".conf
    if [ $? -eq 0 ]; then
        echo "Arquivo de configuração do virtual host $virtualhost editado com sucesso."
    else
        echo "Falha ao editar arquivo de configuração do virtual host $virtualhost."
        exit 1
    fi

    # Ativar o virtual host
    sudo a2ensite "$virtualhost".conf
    if [ $? -eq 0 ]; then
        echo "Virtual host $virtualhost ativado com sucesso."
    else
        echo "Falha ao ativar o virtual host $virtualhost."
        exit 1
    fi
done

# Desativar o site padrão
sudo a2dissite 000-default.conf
if [ $? -eq 0 ]; then
    echo "Site padrão desativado com sucesso."
else
    echo "Falha ao desativar o site padrão."
    exit 1
fi

# Testar configuração do Apache
sudo apache2ctl configtest
if [ $? -eq 0 ]; then
    echo "Configuração do Apache testada com sucesso."
else
    echo "Falha ao testar a configuração do Apache."
    exit 1
fi

# Recarregar o Apache
sudo systemctl reload apache2
if [ $? -eq 0 ]; then
    echo "Apache recarregado com sucesso."
else
    echo "Falha ao recarregar o Apache."
    exit 1
fi

# shellcheck disable=SC2145
echo "Configuração concluída para os virtual hosts: $@"

sudo update-alternatives --set php /usr/bin/php5.6

sudo apt-get install php5.6-xml
sudo apt install php5.6-mysql
sudo apt install php5.6-mysqli
sudo service apache2 restart
