import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { TokenService } from '../../admin-service/token/token.service';
import { AdminConfig } from '../../AdminConfig';
import { ContractDetailsResponse } from '../entity/contract-details-response';
import { ContractsResponse } from '../entity/contracts-response';

@Injectable({
  providedIn: 'root'
})
export class ContractsService {


  constructor(
    private httpClient: HttpClient, 
    private tokenService: TokenService) {}

  private static errorHandle(error: HttpErrorResponse) {
  return throwError(error || 'Server Error');
  }

  // Get All Day Off Captains
  allPendingContracts(): Observable<ContractsResponse> {    
  return this.httpClient.get<ContractsResponse>(
    AdminConfig.pendingContractsAPI, 
    this.tokenService.httpOptions()
    ).pipe(catchError(ContractsService.errorHandle));
  }

  contractDetails(contractID: number): Observable<ContractDetailsResponse> {    
    return this.httpClient.get<ContractDetailsResponse>(
      `${AdminConfig.contractDetailsAPI}/${contractID}`, 
      this.tokenService.httpOptions()
    ).pipe(catchError(ContractsService.errorHandle));
  }

  contractAccept(data):Observable<any> {
    return this.httpClient.put<any>(
      AdminConfig.contractAcceptAPI, 
      JSON.stringify(data),
      this.tokenService.httpOptions()
    ).pipe(catchError(ContractsService.errorHandle));
  }

}