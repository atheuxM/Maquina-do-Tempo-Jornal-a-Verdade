import datetime
import json
from feedgen.feed import FeedGenerator

def gerar_feed_maquina_do_tempo():
    # 1. Configuração do Fuso Horário (Brasília UTC-3)
    fuso = datetime.timezone(datetime.timedelta(hours=-3))
    hoje = datetime.datetime.now(fuso)
    dia_ano_hoje = hoje.strftime('%m-%d')
    ano_atual = hoje.year

    # 2. Setup do Feed
    fg = FeedGenerator()
    fg.id('maquina-do-tempo-jornal')
    fg.title('Máquina do Tempo - Jornal A Verdade')
    fg.description(f'Artigos históricos publicados em {hoje.strftime("%d/%m")}')
    fg.link(href='https://github.com/seu-usuario/repo', rel='alternate')
    fg.language('pt-br')

    # 3. Carregar o JSON otimizado
    try:
        with open('materias_url_averdade.json', 'r', encoding='utf-8') as f:
            biblioteca = json.load(f)
    except FileNotFoundError:
        print("Erro: arquivo noticias.json não encontrado.")
        return

    # 4. Buscar notícias do dia
    noticias_de_hoje = biblioteca.get(dia_ano_hoje, [])

    if not noticias_de_hoje:
        # Opcional: Adicionar um item informando que não houve notícias neste dia na história
        fe = fg.add_entry()
        fe.id(f'vazio-{dia_ano_hoje}-{ano_atual}')
        fe.title(f'Nenhum artigo histórico encontrado para {hoje.strftime("%d/%m")}')
        fe.description('O museu está silencioso hoje.')
        fe.link(href='https://google.com')
    else:
        for noticia in noticias_de_hoje:
            fe = fg.add_entry()
            # O TRUQUE DO GUID: Ano Atual + URL original
            # Isso garante que em 2027 o leitor de RSS veja como "novo"
            guid = f"{ano_atual}-{noticia['url']}"
            fe.id(guid)
            
            # Título com o ano original para dar contexto histórico
            fe.title(f"[{noticia['ano']}] {noticia['titulo']}")
            fe.link(href=noticia['url'])
            fe.description(f"Artigo publicado originalmente em {noticia['ano']}.")
            fe.pubDate(hoje)

    # 5. Salvar o arquivo para o GitHub Pages
    fg.rss_file('feed.xml')

if __name__ == '__main__':
    gerar_feed_maquina_do_tempo()
