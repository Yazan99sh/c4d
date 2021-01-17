import 'package:c4d/module_init/ui/screens/init_account_screen/init_account_screen.dart';
import 'package:flutter/material.dart';

abstract class InitAccountState {
  final InitAccountScreenState screen;
  InitAccountState(this.screen);

  Widget getUI(BuildContext context);
}

class initAccountStateInit extends InitAccountState {
  initAccountStateInit(InitAccountScreenState screen) : super(screen) {
    screen.getPackages();
  }

  @override
  Widget getUI(BuildContext context) {
    return Text('Init Data');
  }
}

class InitAccountStateLoading extends InitAccountState {
  InitAccountStateLoading(screen) : super(screen);

  @override
  Widget getUI(BuildContext context) {
    return Scaffold(
      body: Center(child: Text('Loading')),
    );
  }
}

class InitAccountStateError extends InitAccountState {
  final String errorMsg;

  InitAccountStateError(
    this.errorMsg,
    InitAccountScreenState screen,
  ) : super(screen);

  @override
  Widget getUI(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Text(errorMsg),
      ),
    );
  }
}

class InitAccountStateSubscribeSuccess extends InitAccountState {
  InitAccountStateSubscribeSuccess(InitAccountScreenState screen)
      : super(screen);
  @override
  Widget getUI(BuildContext context) {
    return Scaffold();
  }
}
