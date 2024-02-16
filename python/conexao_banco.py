import mysql.connector  # Certifique-se de instalar o pacote 'mysql-connector-python' com 'pip install mysql-connector-python'

def conectar_banco():
    try:
        # Substitua 'seu_usuario', 'sua_senha', 'seu_banco' pelos dados do seu banco
        conexao = mysql.connector.connect(
            user='root',
            password='',
            host='localhost',
            database='replichoice_master'
        )

        print("Conexão bem-sucedida!")
        return conexao

    except mysql.connector.Error as e:
        print(f"Erro ao conectar ao banco de dados: {e}")
        return None

def fechar_conexao(conexao):
    if conexao:
        conexao.close()
        print("Conexão fechada.")

# Restante do script...
