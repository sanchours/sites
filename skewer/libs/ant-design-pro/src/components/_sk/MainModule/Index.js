import React, {useEffect} from 'react';
import {connect} from "dva";
import AuthForm from "../AuthForm/Index";
import Layout from '../Layout/Index';

const MainModule = ({moduleData, ...rest}) => {

  if (!moduleData)
    return null;

  let mainModule = null;

  useEffect(()=> {
    const init = moduleData.get('init') ? moduleData.get('init').toJS() : false;
    if (init) {
      for (let key in init) {
        window[key] = init[key];
      }}
  }, []);

  if ( moduleData.get('moduleName') === 'Auth' ){
    mainModule =  <AuthForm {...rest} />;
  } else if ( moduleData.get('moduleName') === 'Layout' ){
    mainModule =  <Layout moduleData={moduleData} {...rest} />;
  }

  return mainModule;
};


export default connect(({ skGlobal }) => {
  return {
    moduleData: skGlobal.outLayout.get('out'),
  };
})(MainModule);
