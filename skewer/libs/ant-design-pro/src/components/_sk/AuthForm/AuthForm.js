import React from 'react';
import {Button, Col, Form, Icon, Row, Input} from "antd";
import {connect} from "dva";
import CanapeLogo from "../CanapeLogo/Index"

const AuthForm = ({form, moduleData, dispatch}) => {

  const handleClickOnButtonCanapeId = () => {
    const hash = window.location.hash.replace('#','%23');
    window.location.replace("/sys.php?return_link=" + window.location.pathname + hash);
  };

  const handleClickOnForgotPassword = (e) => {
    e.preventDefault();
    dispatch({
      type: 'skGlobal/forgotPassword',
      payload: {path: 'out'}
    });
  };

  const handleClickOnButtonEnter = (e) => {
    e.preventDefault();
    dispatch({
      type: 'skGlobal/login',
      payload: {
        path: 'out',
        login: form.getFieldValue('username'),
        pass: form.getFieldValue('password')
      }
    });
  };

  const { getFieldDecorator } = form;

  const langValues = moduleData.getIn(['init', 'lang']);

  return (
    <div className="b-form-wrapper">

      <Form className="login-form">
        <CanapeLogo />

        <h1 className="login-form__header">{langValues.get('authPanelTitle')}</h1>
        <Form.Item className="login-form__item">
          {getFieldDecorator('username', {
            rules: [{ required: false, message: 'Please input your username!' }],
          })(
            <Input
              className="login-form__input"
              prefix={<Icon type="user" style={{ color: 'rgba(0,0,0,.25)' }} />}
              placeholder={langValues.get('authLoginTitle')}
            />,
          )}
        </Form.Item>

        <Form.Item className="login-form__item">
          {getFieldDecorator('password', {
            rules: [{ required: false, message: '' }],
          })(
            <Input.Password
              className="login-form__input"
              prefix={<Icon type="lock" style={{ color: 'rgba(0,0,0,.25)' }} />}
              type="password"
              placeholder={langValues.get('authPassTitle')}
            />,
          )}
        </Form.Item>

        <Row gutter={16}>
          <Col span={12}>
            <Button
              type="primary"
              className="login-form-button login-form-button__can-id"
              onClick={handleClickOnButtonCanapeId}
            >
              {langValues.get('authCanapeId')}
            </Button>
          </Col>

          <Col span={12}>
            <Button
              htmlType="submit"
              type="primary"
              className="login-form-button"
              onClick={handleClickOnButtonEnter}
            >
              {langValues.get('authLoginButton')}
            </Button>
          </Col>
        </Row>
        <Row className="form-forgot">
          <a href="#" onClick={handleClickOnForgotPassword}>{langValues.get('authForgotPass')}</a>
        </Row>

      </Form>
    </div>
  );
};

const FormWrapped = Form.create({
  name: 'normal_login',
})(AuthForm);

export default connect(({ skGlobal }) => {
  return {
    moduleData: skGlobal.outLayout.get('out'),
  };
})(FormWrapped);
