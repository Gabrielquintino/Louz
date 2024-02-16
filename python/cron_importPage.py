from conexao_banco import conectar_banco, fechar_conexao
from ImportPage import ImportPage

def verificar_tabela():
    # Conecta ao banco de dados
    conexao = conectar_banco()

    try:
        # Verifica se existe registro com status 'waiting'
        cursor = conexao.cursor()
        cursor.execute("SELECT id, name, email, phone, url FROM import WHERE status = 'waiting'")
        arr_register = cursor.fetchall()

        if arr_register:
            for register in arr_register:
                ImportPage(register)

        else:
            print("Nenhum registro com status 'waiting' encontrado.")

    except Exception as e:
        print(f"Erro ao verificar tabela: {e}")

    finally:
        # Fecha a conexão com o banco de dados
        fechar_conexao(conexao)

# Exemplo de uso
if __name__ == "__main__":
    verificar_tabela()
