import pandas as pd
import base64
import re
from datetime import datetime

# http://stopcoronavirus.mcgm.gov.in/insights-on-map
# curl http://3.21.5.119/COVID19/containment_zones/ > Downloads/tmp.html
# Do curl on this
# Get the webpage and then create csv from sublime
# search each feature grp to get Lat Long and address-base64:

# .addTo(feature_group_78c7355fcab94a4caf4e74de702452f1
# .addTo(feature_group_78c7355fcab94a4caf4e74de702452f1)
#


df = pd.read_csv('htdocs/all_scape.csv')


def parse_base64(x):
    try:
        if not x['Base64'].endswith('=='):
            x['Base64'] += "=="
        info_html = base64.b64decode(x['Base64']).decode('ascii', 'ignore')
        info_html = info_html.split('</b>')
        issue_type = info_html[1].split('<br>')[0]
        # Get red/blue or orange color
        issue_type = re.split('-| ', issue_type)[0].strip()
        issue_type = "Yellow" if issue_type == "Blue" else issue_type

        address = re.escape("<b> BMC verified </b><br>" + info_html[2].split('<br>')[1].strip().replace('\n', '<br>'))
        return {'issue_color': issue_type, 'address': address}
    except Exception as e:
        print("Issue ", e, x)
        raise e


final_df = pd.concat([df,
                      df[['Base64']].apply(lambda x: pd.Series(parse_base64(x)), 1)
                      ], 1)

final_df['reported_date'] = datetime.strftime(datetime.now(), '%Y-%m-%d %H:%M:%S')

final_df['is_verifed_BMC'] = 1
final_df = final_df.rename(columns={'Long': 'lng', "Type": "base_type", "Lat": "lat"})
final_df['lat'] = final_df['lat'].astype(str).str.strip().astype(float)
final_df['lng'] = final_df['lng'].astype(str).str.strip().astype(float)

final_lst = final_df.values.tolist()

a = ['({})'.format(','.join(['{}'.format(w) for w in i])) for i in final_lst]
a = ['%r' % (tuple(i),) for i in final_lst]
with open('bmc_verified_db.sql', 'w') as f:
    f.write(',\n'.join(a))
