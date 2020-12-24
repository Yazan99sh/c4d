import { Injectable } from '@angular/core';
import { Action, AngularFirestore, DocumentSnapshot } from '@angular/fire/firestore';
import { Observable } from 'rxjs';
import { MessageModel } from '../model/message-model';

@Injectable({
  providedIn: 'root'
})
export class MessagesService {

  constructor(private firestore: AngularFirestore) { }

  // This sends the message
  sendMessage(chatRoomId: string, message: string, clientId: string): void {
    const chatMsg: MessageModel = {
      msg: message,
      sender: clientId,
      sentDate: Date.now().toString()
    };
    this.firestore.collection('chat_rooms')
      .doc(chatRoomId)
      .collection('messages').add(chatMsg);
  }

  // This Listens to messages changes in the chat room
  getMessagesObservable(chatRoomId: string): Observable<Action<DocumentSnapshot<any>>> {
    return this.firestore.collection('chat_rooms')
      .doc(chatRoomId).snapshotChanges();
  }
}
