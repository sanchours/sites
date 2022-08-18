import React from 'react';
import { connect } from "dva";
import { Row } from "antd";
import imgSuccess from "./files/mail-success.png";

const SuccessForm = ({ moduleData }) => {

  const langValues = moduleData.getIn(['params', 'lang']);

  return (
    <div className="b-form-wrapper">
      <div className="login-form success-message">
        <img className="success-message__pic" src={imgSuccess} alt="success" />
        <h1 className="login-form__header">
          {langValues.get('passwords_recovery')}
        </h1>
        <div className="success-message__text">
          {langValues.get('msg_recover_instruct')}
        </div>
        <Row className="form-forgot">
          <a href="/admin">{langValues.get('back_check')}</a>
        </Row>
      </div>
    </div>
  );
};

export default connect(({ skGlobal }) => {
  return {
    moduleData: skGlobal.outLayout.get('out'),
  };
})(SuccessForm);
