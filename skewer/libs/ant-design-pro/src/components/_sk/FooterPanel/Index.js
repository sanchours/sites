import React from 'react'
import {connect} from 'dva'
import styles from './Index.less'
import _ from 'lodash'

const FooterPanel = ({moduleData}) => {

  const {logoImg, version, hideCopyright, sDataSupport, sSwitch} = moduleData.getIn(['init']).toJS();

  const {
    link_developer,
    end_service,
    end_href,
    support_rates,
    end_service_rates,
    end_service_rates_link,
    need_tech_support,
    need_active_support,
    link_help
  } = moduleData.getIn(['init', 'lang']).toJS();

  const handleClick = () => {
    window.open(link_help,'_blank');
  };

  let content = '';

  if ( sDataSupport ){
    content = (<p>{end_service} <b> { sDataSupport } </b> </p>);
    if ( _.trim(end_href) ){
      content = (<>{content}<p><a href={end_href} target="_blank">{support_rates}</a></p></>);
    }
  } else if ( sSwitch === 'end' ){
      content = (
        <p>
          {end_service_rates}
          { _.trim(end_href) ? (
            <a href={end_href} target="_blank">{end_service_rates_link}</a>
          ) : null }
        </p>
      );
  } else if ( sSwitch === 'no' ){
    content = (
      <p>
        {need_tech_support}
        { _.trim(end_href) ? <a href={end_href} target="_blank">{need_active_support}</a> : null }
      </p>
    );
  }


  return (
    <div className={styles.bFooter}>
      <div className={styles.FooterBox}>
        <p className={styles.CanapeLogo}><img src={logoImg} alt=""/></p>
        { !hideCopyright ? (<p><a href={link_developer} target="_blank">CanapeCMS</a></p>) : null }
        <p>Skewer {version}</p>
        {content}
        <div className="g-clear"></div>
      </div>
    </div>
  );

};

export default connect(({skGlobal}) => {
  return {
    moduleData: skGlobal.footerLayout.get('out.footer')
  }
})(FooterPanel);
