import React from 'react';
import { Layout } from 'antd';
import {connect} from 'dva'
import LogPanel from '../../components/_sk/LogPanel/Index';
import FooterPanel from "../../components/_sk/FooterPanel/Index";

const { Footer } = Layout;

const Index = ({logData, isMobile}) => {

  if (isMobile)
    return '';

  return (
    <Footer style={{ padding: 0 }}>
      { logData ? <LogPanel /> : <FooterPanel /> }
    </Footer>
  );
};

export default connect(({skGlobal}) => {
  return {
    logData: skGlobal.logLayout.get('out.log')
  };
})(Index)
