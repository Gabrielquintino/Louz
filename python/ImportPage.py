from typing import Self
import requests
import os
import unicodedata
import re
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from bs4 import BeautifulSoup
from urllib.parse import urljoin
from conexao_banco import conectar_banco, fechar_conexao

class ImportPage:

    def __init__(self, arr_register):
        self.start(arr_register)

    def start(self, arr_register):

        conexao = conectar_banco()

        id = arr_register[0]
        name = arr_register[1]
        email = arr_register[2]
        phone = arr_register[3]
        url = arr_register[4]

        # Configura as opções do Chrome para o modo headless
        chrome_options = Options()
        chrome_options.add_argument("--headless")
        chrome_options.add_argument("--disable-gpu")     

        # Abre uma instância do navegador Chrome (certifique-se de ter o ChromeDriver instalado e no PATH)
        driver = webdriver.Chrome(options=chrome_options)

        # Navega até a URL fornecida
        driver.get(url)

        # Obtém o conteúdo da página
        conteudo = driver.page_source

        if conteudo:

            code = conteudo
            # Analisa o HTML com BeautifulSoup
            soup = BeautifulSoup(code, 'html.parser')

            # Encontra todas as tags que podem conter referências a outros tipos de arquivos
            arr_references = soup.find_all(['link', 'script', 'img', 'a', 'source', 'iframe', 'audio', 'video', 'embed'])
            
            root_path = os.path.join(os.getcwd(), 'temp', str(id))
            os.makedirs(root_path, exist_ok=True)
            caminho_arquivo = os.path.join(root_path, 'index.html')
            with open(caminho_arquivo, 'w', encoding='utf-8') as arquivo:
                arquivo.write(conteudo)

            # Loop sobre as tags encontradas
            for tag in arr_references:
                if tag.name == 'link':
                    href = tag.get('href')
                    if href and os.path.basename(href) != '':
                        link_absoluto = urljoin(url, href)
                        new_path = self.download_content(id, link_absoluto, 'css', os.path.basename(href))
                        # novo link
                        tag['href'] = tag['href'].replace(href, new_path)
                        # substitui o link antigo pelo novo
                        with open(caminho_arquivo, 'w', encoding='utf-8') as arquivo:
                                arquivo.write(str(soup))

                elif tag.name == 'script':
                    src = tag.get('src')
                    if src and os.path.basename(href) != '':
                        link_absoluto = urljoin(url, src)
                        new_path = self.download_content(id, link_absoluto, 'js', os.path.basename(src))
                        # novo link
                        tag['src'] = tag['src'].replace(href, new_path)
                        # substitui o link antigo pelo novo
                        with open(caminho_arquivo, 'w', encoding='utf-8') as arquivo:
                                arquivo.write(str(soup))                        

                elif tag.name == 'img':
                    src = tag.get('src')
                    if src and os.path.basename(href) != '':
                        link_absoluto = urljoin(url, src)
                        new_path = self.download_content(id, link_absoluto, 'img', os.path.basename(src))
                        # novo link
                        tag['src'] = tag['src'].replace(href, new_path)
                        # substitui o link antigo pelo novo
                        with open(caminho_arquivo, 'w', encoding='utf-8') as arquivo:
                                arquivo.write(str(soup))                        

                else:
                    # Para qualquer outra tag, baixa na pasta 'extra'
                    # Adicione mais verificações conforme necessário
                    pass

        else:
            print(f'Oops, algo deu errado. Não foi possível obter o conteúdo')
            str_query = "UPDATE import SET status = 'error' WHERE id = '"+ str(id) + "';"
            cursor = conexao.cursor()
            cursor.execute(str_query)

        driver.quit()
        
        fechar_conexao(conexao)

    # Função para baixar o conteúdo e salvar em um arquivo
    def download_content(self, id, url, pasta, nome_arquivo):
        root_path = os.path.join(os.getcwd(), 'temp', str(id))
        nome_arquivo = self.normalize_string_with_auto_extension(nome_arquivo, root_path + '/' + pasta)
        
        os.makedirs(root_path, exist_ok=True)
        os.makedirs(root_path + '/' + pasta, exist_ok=True)
        conteudo = requests.get(url).content
        caminho_arquivo = os.path.join(root_path, pasta, nome_arquivo)

        if pasta == 'css':
            urls = re.findall(r'url\([\'\"]?(.*?)[\'\"]?\)', conteudo.decode('utf-8'))

            for url in urls:
                if not url.startswith(('http:', 'https:')):
                    continue  # Ignorar URLs externas completas

                # Fazer o download do arquivo
                response = requests.get(url)

                # Decodificar os bytes da resposta
                conteudo_arquivo = response.content.decode('utf-8')
                
                nome_arquivo = os.path.join(caminho_arquivo, self.normalize_string_with_auto_extension(os.path.basename(url)))

                with open(nome_arquivo, 'w', encoding='utf-8') as arquivo_download:
                    arquivo_download.write(conteudo_arquivo)

                # Substituir a referência no CSS pelo caminho local
                conteudo = conteudo.replace(url, nome_arquivo)

        with open(caminho_arquivo, 'w') as arquivo:
            arquivo.write(conteudo)
        print(f'Conteúdo baixado e salvo em: {caminho_arquivo}')
        return pasta + '/' + nome_arquivo


    def normalize_string_with_auto_extension(self, s, pasta_destino):
        if '?' in s:
            partes = s.split('?')
            primeira_metade = partes[0]
            extensao = primeira_metade.split('.')[-1]
            resultado = primeira_metade.replace(f".{extensao}", "") + f".{extensao}"
        else:
            extensao = s.split('.')[-1]
            resultado = s.replace(f".{extensao}", "") + f".{extensao}"

        caminho_completo = os.path.join(pasta_destino, resultado)
        
        # Verifica se o arquivo já existe na pasta
        contador = 1
        while os.path.exists(caminho_completo):
            # Adiciona uma variação ao nome do arquivo
            resultado = f"{resultado.split('.')[0]}_{contador}.{extensao}"
            caminho_completo = os.path.join(pasta_destino, resultado)
            contador += 1

        return resultado



