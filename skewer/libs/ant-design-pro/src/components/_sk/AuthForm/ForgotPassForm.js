import React, { useState } from 'react';
import { Button, Col, Form, Icon, Input, Row } from "antd";
import { connect } from "dva";
import CanapeLogo from "../CanapeLogo/Index";

const random = () => {
  return Math.random() * 1000
};

const ForgotPassForm = ({ form, moduleData, dispatch }) => {
  const { getFieldDecorator } = form;
  const langValues = moduleData.getIn(['init', 'lang']);
  const [captchaVersion, setCaptchaVersion] = useState(random);

  const handleClickOnButtonRecovery = (e) => {
    e.preventDefault();

    dispatch({
      type: 'skGlobal/checkForgot',
      payload: {
        path: 'out',
        login: form.getFieldValue('email'),
        captcha: form.getFieldValue('captcha')
      }
    })
      .then(() => {
        return true;
      })
      .catch(() => {
        setCaptchaVersion(random);
      });
  };

  return (
    <div className="b-form-wrapper">
      <Form layout="horizontal" className="login-form forgot-form">
        <CanapeLogo />

        <h1 className="login-form__header">
          {langValues.get('forgotPass')}
        </h1>

        <Form.Item
          className="login-form__item"
          help={langValues.get('forgotLoginPass')}
        >
          {getFieldDecorator('email', {
            rules: [{ type: 'email', required: false, message: '' }],
          })(
            <Input
              className="login-form__input"
              prefix={<Icon type="mail" style={{ color: 'rgba(0,0,0,.25)' }} />}
              placeholder={langValues.get('email_forgot')}
            />,
          )}
        </Form.Item>

        <Form.Item
          className="login-form__item"
        >
          <div className="forgot-form__cap-wrapper login-form__input">
            <img
              src={`/ajax/captcha.php?v=${captchaVersion}`} alt=''
              onClick={() => setCaptchaVersion(random)}
            />
            {getFieldDecorator('captcha', {
              rules: [{ required: false, message: '' }],
            })(
              <Input className="login-form__input" />,
            )}
          </div>
        </Form.Item>

        <Row>
          <Col>
            <Button
              type="primary"
              className="login-form-button"
              onClick={handleClickOnButtonRecovery}
            >
              {langValues.get('forgotSend')}
            </Button>
          </Col>
        </Row>

        <Row className="form-forgot">
          <a href="/admin">{langValues.get('back_check')}</a>
        </Row>
      </Form>
    </div>
  );
};

const FormWrapped = Form.create({
  name: 'forgot_pass',
})(ForgotPassForm);

export default connect(({ skGlobal }) => {
  return {
    moduleData: skGlobal.outLayout.get('out'),
  };
})(FormWrapped);
