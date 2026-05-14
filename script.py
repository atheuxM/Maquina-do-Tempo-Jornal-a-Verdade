import datetime
from feedgen.feed import FeedGenerator

def generate_calendar_feed():
    fg = FeedGenerator()
    fg.id('http://exemplo.com/calendario-rss')
    fg.title('Calendário Diário - Máquina do Tempo')
    fg.author({'name': 'Seu Nome', 'email': 'seu@email.com'})
    fg.link(href='https://github.com/seu-usuario/seu-repo', rel='alternate')
    fg.description('Um feed de teste que informa o dia do ano.')
    fg.language('pt-br')

    # Obter dados de hoje
    hoje = datetime.datetime.now()
    dia_do_ano = hoje.timetuple().tm_yday
    data_formatada = hoje.strftime('%d/%m/%Y')

    # Criar o item do feed
    fe = fg.add_entry()
    fe.id(f'dia-{data_formatada}')
    fe.title(f'Hoje é dia {data_formatada}')
    fe.description(f'Estamos no {dia_do_ano}º dia do ano de {hoje.year}.')
    fe.link(href=f'https://google.com/search?q={data_formatada}')
    fe.pubDate(datetime.datetime.now(datetime.timezone.utc))

    # Salvar o arquivo
    fg.rss_file('feed.xml')

if __name__ == '__main__':
    generate_calendar_feed()