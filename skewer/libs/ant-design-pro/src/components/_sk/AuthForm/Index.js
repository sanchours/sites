import React from 'react';
import { connect } from 'dva'
import style from './style.less'
import ForgotPassForm from "./ForgotPassForm";
import SuccessForm from "./SuccessForm";
import AuthForm from "./AuthForm";
import RecoveryForm from "./RecoveryForm";
import * as sk from "../../../services/_sk/api";


@connect(({ skGlobal }) => {
  return {
    moduleData: skGlobal.outLayout.get('out'),
  };
})
class Index extends React.Component {

  componentDidUpdate(prevProps, prevState, snapshot) {

    const {moduleData} = this.props;

    const params = moduleData.get('params');

    if ( params.get('cmd') === 'login' ){
      // если авторизация не удалась
      if ( !params.get('success') ) {
        // sk.error( params.notice );
      } else {
        window.location.reload();
      }
    }

  }


  render() {

    const {moduleData, dispatch} = this.props;
    const token = window.sk.getUrlParam('token');
    const cmd = moduleData.getIn(['params', 'cmd']);
    const success = moduleData.getIn(['params', 'success']);
    const notice = moduleData.getIn(['params', 'notice']);
    const errorTitle = moduleData.getIn(['init', 'dict', 'error']);

    let renderComponent = null;

    if (token && cmd === 'init') {
      dispatch({
        type: 'skGlobal/checkTokenForChangePassword',
        payload: {
          path: 'out',
          token
        }
      });
    }

    switch (cmd){
      case 'checkForgot':
        const loginError = moduleData.getIn(['params', 'login']);
        if (loginError)
          sk.error(errorTitle, loginError);

        const captchaError = moduleData.getIn(['params', 'captcha']);
        if (captchaError)
          sk.error(errorTitle, captchaError);

        renderComponent = <ForgotPassForm />;
        break;

      case 'ForgotPass':
        renderComponent = <ForgotPassForm />;
        break;

      case 'RecoveryForm':
        if (!success && notice) {
          sk.error(errorTitle, notice);
        }

        renderComponent = <RecoveryForm token={token}/>;
        break;

      case 'Success':
        renderComponent = <SuccessForm />;
        break;

      case 'login':
        if (!success && notice) {
          sk.error(errorTitle, notice);
        }

        renderComponent = <AuthForm />;
        break;

      default:
        renderComponent = <AuthForm />
    }

    return renderComponent;

  }
}


export default Index
